@extends('admin.layouts.app')


@section('title', 'User Management')

@section('page-title', 'User Management')


@section('content')

    {{-- =========================================================
    PAGE HEADER
    ========================================================== --}}
    <div class="admin-users-page-head">

        <div>

            <h2>
                User Management
            </h2>

            <p>
                Manage registered buyers and sellers, account access,
                verification status and administrator access.
            </p>

        </div>


        <div class="admin-users-header-meta">

            <span>
                <i class="fa-solid fa-users"></i>

                {{ number_format($stats['total'] ?? 0) }}
                registered
            </span>

        </div>

    </div>



    {{-- =========================================================
    SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="admin-success-alert">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif



    {{-- =========================================================
    ERROR MESSAGE
    ========================================================== --}}
    @if($errors->any())

        <div class="admin-users-error-alert">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                @foreach($errors->all() as $error)

                    <div>
                        {{ $error }}
                    </div>

                @endforeach

            </div>

        </div>

    @endif



    {{-- =========================================================
    STATISTICS
    ========================================================== --}}
    <div class="admin-users-stat-grid">


        {{-- Total --}}
        <div class="admin-card admin-users-stat-card">

            <div class="admin-users-stat-content">

                <span class="admin-users-stat-label">
                    Total Users
                </span>

                <strong class="admin-users-stat-value">
                    {{ number_format($stats['total'] ?? 0) }}
                </strong>

                <small>
                    Registered customer accounts
                </small>

            </div>


            <div class="admin-users-stat-icon total">

                <i class="fa-solid fa-users"></i>

            </div>

        </div>



        {{-- Active --}}
        <div class="admin-card admin-users-stat-card">

            <div class="admin-users-stat-content">

                <span class="admin-users-stat-label">
                    Active
                </span>

                <strong class="admin-users-stat-value">
                    {{ number_format($stats['active'] ?? 0) }}
                </strong>

                <small>
                    Accounts allowed to sign in
                </small>

            </div>


            <div class="admin-users-stat-icon active">

                <i class="fa-solid fa-user-check"></i>

            </div>

        </div>



        {{-- Inactive --}}
        <div class="admin-card admin-users-stat-card">

            <div class="admin-users-stat-content">

                <span class="admin-users-stat-label">
                    Inactive
                </span>

                <strong class="admin-users-stat-value">
                    {{ number_format($stats['inactive'] ?? 0) }}
                </strong>

                <small>
                    Access currently suspended
                </small>

            </div>


            <div class="admin-users-stat-icon inactive">

                <i class="fa-solid fa-user-slash"></i>

            </div>

        </div>



        {{-- Verified --}}
        <div class="admin-card admin-users-stat-card">

            <div class="admin-users-stat-content">

                <span class="admin-users-stat-label">
                    Email Verified
                </span>

                <strong class="admin-users-stat-value">
                    {{ number_format($stats['verified'] ?? 0) }}
                </strong>

                <small>
                    Completed email verification
                </small>

            </div>


            <div class="admin-users-stat-icon verified">

                <i class="fa-solid fa-envelope-circle-check"></i>

            </div>

        </div>

    </div>



    {{-- =========================================================
    FILTER / SEARCH
    ========================================================== --}}
    <div class="admin-card admin-users-toolbar">

        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-users-filter-form">

            {{-- Search --}}
            <div class="admin-users-search">

                <i class="fa-solid fa-magnifying-glass"></i>


                <input type="search" name="search" value="{{ request('search') }}"
                    placeholder="Search name, email or phone..." autocomplete="off">

            </div>



            {{-- Status --}}
            <select name="status" aria-label="Filter by status">

                <option value="">
                    All statuses
                </option>


                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>


                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </select>



            {{-- Verification --}}
            <select name="verification" aria-label="Filter by verification">

                <option value="">
                    All verification
                </option>


                <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>
                    Verified
                </option>


                <option value="unverified" {{ request('verification') === 'unverified' ? 'selected' : '' }}>
                    Unverified
                </option>

            </select>



            {{-- Preferred Role --}}
            <select name="preferred_role" aria-label="Filter by account preference">

                <option value="">
                    Buyer & seller
                </option>


                <option value="seller" {{ request('preferred_role') === 'seller' ? 'selected' : '' }}>
                    Seller preference
                </option>


                <option value="buyer" {{ request('preferred_role') === 'buyer' ? 'selected' : '' }}>
                    Buyer preference
                </option>

            </select>



            {{-- Filter --}}
            <button type="submit" class="admin-users-filter-button">

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>



            {{-- Reset --}}
            @if(
                    request()->filled('search')
                    ||
                    request()->filled('status')
                    ||
                    request()->filled('verification')
                    ||
                    request()->filled('preferred_role')
                )

                <a href="{{ route('admin.users.index') }}" class="admin-users-reset">
                    Reset
                </a>

            @endif

        </form>

    </div>



    {{-- =========================================================
    USER TABLE CARD
    ========================================================== --}}
    <div class="admin-card admin-users-table-card">


        {{-- =====================================================
        TABLE HEADER
        ====================================================== --}}
        <div class="admin-users-table-card-head">

            <div>

                <h3>
                    Registered Users
                </h3>

                <p>

                    @if($users->total() > 0)

                        Showing

                        <strong>
                            {{ $users->firstItem() }}
                        </strong>

                        –

                        <strong>
                            {{ $users->lastItem() }}
                        </strong>

                        of

                        <strong>
                            {{ $users->total() }}
                        </strong>

                    @else

                        No customer accounts found.

                    @endif

                </p>

            </div>


            <div class="admin-users-table-icon">

                <i class="fa-solid fa-address-book"></i>

            </div>

        </div>



        {{-- =====================================================
        TABLE
        ====================================================== --}}
        <div class="admin-users-table-wrapper">

            <table class="admin-users-table">

                <thead>

                    <tr>

                        <th>
                            User
                        </th>

                        <th>
                            Contact
                        </th>

                        <th>
                            Preferred View
                        </th>

                        <th>
                            Verification
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Last Login
                        </th>

                        <th class="admin-users-actions-heading">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($users as $user)

                                    <tr>


                                        {{-- =========================================
                                        USER
                                        ========================================== --}}
                                        <td>

                                            <div class="admin-users-person">

                                                <div class="admin-users-avatar">

                                                    {{
                            strtoupper(
                                mb_substr(
                                    $user->name ?? 'U',
                                    0,
                                    1
                                )
                            )
                                                            }}

                                                </div>


                                                <div class="admin-users-person-info">

                                                    <strong>
                                                        {{ $user->name }}
                                                    </strong>


                                                    <span>
                                                        User ID #{{ $user->id }}
                                                    </span>


                                                    <small>
                                                        Joined
                                                        {{ optional($user->created_at)->format('M d, Y') }}
                                                    </small>

                                                </div>

                                            </div>

                                        </td>



                                        {{-- =========================================
                                        CONTACT
                                        ========================================== --}}
                                        <td>

                                            <div class="admin-users-contact">

                                                <span>

                                                    <i class="fa-regular fa-envelope"></i>

                                                    {{ $user->email }}

                                                </span>


                                                <span>

                                                    <i class="fa-solid fa-phone"></i>

                                                    {{ $user->phone ?: 'No phone added' }}

                                                </span>

                                            </div>

                                        </td>



                                        {{-- =========================================
                                        PREFERRED ROLE
                                        ========================================== --}}
                                        <td>

                                            @if($user->preferred_role === 'buyer')

                                                <span class="admin-users-role-badge buyer">

                                                    <i class="fa-solid fa-bag-shopping"></i>

                                                    Buyer

                                                </span>

                                            @else

                                                <span class="admin-users-role-badge seller">

                                                    <i class="fa-solid fa-store"></i>

                                                    Seller

                                                </span>

                                            @endif

                                        </td>



                                        {{-- =========================================
                                        EMAIL VERIFICATION
                                        ========================================== --}}
                                        <td>

                                            @if($user->hasVerifiedEmail())

                                                            <div class="admin-users-verification verified">

                                                                <span>

                                                                    <i class="fa-solid fa-check"></i>

                                                                </span>


                                                                <div>

                                                                    <strong>
                                                                        Verified
                                                                    </strong>

                                                                    <small>
                                                                        {{
                                                optional($user->email_verified_at)
                                                    ->format('M d, Y')
                                                                                    }}
                                                                    </small>

                                                                </div>

                                                            </div>

                                            @else

                                                <div class="admin-users-verification pending">

                                                    <span>

                                                        <i class="fa-regular fa-clock"></i>

                                                    </span>


                                                    <div>

                                                        <strong>
                                                            Pending
                                                        </strong>

                                                        <small>
                                                            Awaiting verification
                                                        </small>

                                                    </div>

                                                </div>

                                            @endif

                                        </td>



                                        {{-- =========================================
                                        STATUS
                                        ========================================== --}}
                                        <td>

                                            <span class="admin-users-status-badge
                                                               {{
                            $user->status
                            ? 'active'
                            : 'inactive'
                                                               }}">

                                                <span></span>


                                                {{
                            $user->status
                            ? 'Active'
                            : 'Inactive'
                                                        }}

                                            </span>

                                        </td>



                                        {{-- =========================================
                                        LAST LOGIN
                                        ========================================== --}}
                                        <td>

                                            <div class="admin-users-last-login">

                                                @if($user->last_login_at)

                                                                    <strong>
                                                                        {{
                                                    $user
                                                        ->last_login_at
                                                        ->diffForHumans()
                                                                                    }}
                                                                    </strong>


                                                                    <span>
                                                                        {{
                                                    $user
                                                        ->last_login_at
                                                        ->format(
                                                            'M d, Y · h:i A'
                                                        )
                                                                                    }}
                                                                    </span>


                                                                    @if($user->last_login_ip)

                                                                        <small>

                                                                            <i class="fa-solid fa-location-dot"></i>

                                                                            {{ $user->last_login_ip }}

                                                                        </small>

                                                                    @endif


                                                @else

                                                    <strong class="never">
                                                        Never logged in
                                                    </strong>

                                                @endif

                                            </div>

                                        </td>



                                        {{-- =========================================
                                        ACTIONS
                                        ========================================== --}}
                                        <td>

                                            <div class="admin-users-actions">


                                                {{-- =====================================
                                                LOGIN AS USER
                                                ====================================== --}}
                                                @if(
                                                                            $user->status
                                                                            &&
                                                                            $user->hasVerifiedEmail()
                                                                        )

                                                                        <form method="POST" action="{{ route(
                                                        'admin.users.impersonate',
                                                        $user
                                                    ) }}" class="admin-users-action-form" onsubmit="return confirm(
                                                                                            'Login as {{ addslashes($user->name) }}? You will temporarily enter this user account.'
                                                                                        );">

                                                                            @csrf


                                                                            <button type="submit" class="admin-users-action login" title="Login as user">

                                                                                <i class="fa-solid fa-right-to-bracket"></i>

                                                                                <span>
                                                                                    Login
                                                                                </span>

                                                                            </button>

                                                                        </form>


                                                @else

                                                                    <button type="button" class="admin-users-action disabled" title="{{
                                                    !$user->status
                                                    ? 'Inactive users cannot be accessed'
                                                    : 'User must verify their email first'
                                                                                    }}" disabled>

                                                                        <i class="fa-solid fa-right-to-bracket"></i>

                                                                        <span>
                                                                            Login
                                                                        </span>

                                                                    </button>

                                                @endif



                                                {{-- =====================================
                                                ACTIVATE / DEACTIVATE
                                                ====================================== --}}
                                                <form method="POST" action="{{ route(
                            'admin.users.status',
                            $user
                        ) }}" class="admin-users-action-form" onsubmit="return confirm(
                                                                '{{ $user->status
                            ? 'Deactivate ' . addslashes($user->name) . '? The user will no longer be able to access their account and will receive an email notification.'
                            : 'Activate ' . addslashes($user->name) . '?'
                                                                }}'
                                                            );">

                                                    @csrf
                                                    @method('PATCH')


                                                    <button type="submit" class="admin-users-action
                                                                       {{
                            $user->status
                            ? 'deactivate'
                            : 'activate'
                                                                       }}" title="{{
                            $user->status
                            ? 'Deactivate user'
                            : 'Activate user'
                                                                }}">

                                                        <i class="fa-solid
                                                                           {{
                            $user->status
                            ? 'fa-user-slash'
                            : 'fa-user-check'
                                                                           }}"></i>


                                                        <span>

                                                            {{
                            $user->status
                            ? 'Deactivate'
                            : 'Activate'
                                                                    }}

                                                        </span>

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>


                    @empty

                        {{-- =========================================
                        EMPTY STATE
                        ========================================== --}}
                        <tr>

                            <td colspan="7" class="admin-users-empty-cell">

                                <div class="admin-users-empty">

                                    <div class="admin-users-empty-icon">

                                        <i class="fa-solid fa-users"></i>

                                    </div>


                                    <h3>
                                        No registered users found
                                    </h3>


                                    <p>

                                        @if(
                                                request()->filled('search')
                                                ||
                                                request()->filled('status')
                                                ||
                                                request()->filled('verification')
                                                ||
                                                request()->filled('preferred_role')
                                            )

                                            No users matched the selected filters.

                                        @else

                                            Buyer and seller accounts will appear
                                            here after registration.

                                        @endif

                                    </p>


                                    @if(
                                            request()->filled('search')
                                            ||
                                            request()->filled('status')
                                            ||
                                            request()->filled('verification')
                                            ||
                                            request()->filled('preferred_role')
                                        )

                                        <a href="{{ route('admin.users.index') }}">

                                            <i class="fa-solid fa-arrow-rotate-left"></i>

                                            Clear filters

                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>



    {{-- =========================================================
    PAGINATION
    ========================================================== --}}
    @if($users->hasPages())

        <div class="admin-users-pagination">

            {{ $users->links() }}

        </div>

    @endif

@endsection



{{-- =============================================================
USER MANAGEMENT STYLES
================================================================ --}}
@push('styles')

    <style>
        /*
    |--------------------------------------------------------------------------
    | User Management - Page Header
    |--------------------------------------------------------------------------
    */

        .admin-users-page-head {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 18px;

        }


        .admin-users-page-head h2 {

            margin: 0;

            color:
                var(--admin-heading);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 20px;

            font-weight: 700;

        }


        .admin-users-page-head p {

            max-width: 650px;

            margin:
                5px 0 0;

            color:
                var(--admin-muted);

            font-size: 10px;

            line-height: 1.6;

        }


        .admin-users-header-meta {

            flex:
                0 0 auto;

        }


        .admin-users-header-meta span {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            height: 36px;

            padding:
                0 12px;

            border:
                1px solid var(--admin-border);

            border-radius: 9px;

            background:
                var(--admin-surface);

            color:
                var(--admin-muted);

            font-size: 12px;

        }


        .admin-users-header-meta i {

            color:
                var(--admin-accent);

        }



        /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

        .admin-users-error-alert {

            display: flex;

            align-items: flex-start;

            gap: 9px;

            margin-bottom: 16px;

            padding:
                11px 13px;

            border:
                1px solid rgba(239, 83, 80, .25);

            border-radius: 10px;

            background:
                rgba(239, 83, 80, .07);

            color:
                #ef5350;

            font-size: 10px;

            line-height: 1.6;

        }



        /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

        .admin-users-stat-grid {

            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 16px;

        }


        .admin-users-stat-card {

            position: relative;

            min-height: 112px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 17px;

        }


        .admin-users-stat-content {

            min-width: 0;

        }


        .admin-users-stat-label {

            display: block;

            color:
                var(--admin-muted);

            font-size: 10px;

        }


        .admin-users-stat-value {

            display: block;

            margin-top: 8px;

            color:
                var(--admin-heading);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 23px;

            line-height: 1;

        }


        .admin-users-stat-content small {

            display: block;

            margin-top: 10px;

            color:
                var(--admin-muted-2);

            font-size:12px;

        }


        .admin-users-stat-icon {

            width: 40px;

            height: 40px;

            flex:
                0 0 40px;

            display: grid;

            place-items: center;

            border-radius: 11px;

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

            font-size: 12px;

        }


        .admin-users-stat-icon.inactive {

            background:
                rgba(239, 83, 80, .10);

            color:
                #ef5350;

        }


        .admin-users-stat-icon.verified {

            background:
                rgba(122, 90, 248, .10);

            color:
                #7a5af8;

        }



        /*
    |--------------------------------------------------------------------------
    | Toolbar
    |--------------------------------------------------------------------------
    */

        .admin-users-toolbar {

            margin-bottom: 16px;

            padding: 13px;

        }


        .admin-users-filter-form {

            display: flex;

            align-items: center;

            gap: 9px;

        }


        .admin-users-search {

            position: relative;

            min-width: 220px;

            flex: 1;

        }


        .admin-users-search i {

            position: absolute;

            top: 50%;

            left: 13px;

            transform:
                translateY(-50%);

            color:
                var(--admin-muted-2);

            font-size: 10px;

            pointer-events: none;

        }


        .admin-users-search input,
        .admin-users-filter-form select {

            height: 40px;

            border:
                1px solid var(--admin-border);

            border-radius: 9px;

            background:
                var(--admin-surface);

            color:
                var(--admin-text);

            font-family: inherit;

            font-size: 10px;

            outline: none;

            transition:
                border-color .15s ease,
                box-shadow .15s ease;

        }


        .admin-users-search input {

            width: 100%;

            padding:
                0 12px 0 35px;

        }


        .admin-users-search input::placeholder {

            color:
                var(--admin-muted-2);

        }


        .admin-users-filter-form select {

            min-width: 135px;

            padding:
                0 28px 0 10px;

        }


        .admin-users-search input:focus,
        .admin-users-filter-form select:focus {

            border-color:
                var(--admin-accent);

            box-shadow:
                0 0 0 3px var(--admin-accent-soft);

        }


        .admin-users-filter-button {

            height: 40px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding:
                0 15px;

            border: 0;

            border-radius: 9px;

            background:
                var(--admin-accent);

            color: #06312e;

            font-family: inherit;

            font-size: 10px;

            font-weight: 700;

            cursor: pointer;

            transition:
                transform .15s ease,
                opacity .15s ease;

        }


        .admin-users-filter-button:hover {

            transform:
                translateY(-1px);

        }


        .admin-users-reset {

            color:
                var(--admin-muted);

            font-size: 12px;

            font-weight: 600;

            text-decoration: none;

        }


        .admin-users-reset:hover {

            color:
                var(--admin-accent);

        }



        /*
    |--------------------------------------------------------------------------
    | Table Card Header
    |--------------------------------------------------------------------------
    */

        .admin-users-table-card {

            overflow: hidden;

        }


        .admin-users-table-card-head {

            min-height: 66px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding:
                15px 17px;

            border-bottom:
                1px solid var(--admin-border);

        }


        .admin-users-table-card-head h3 {

            margin: 0;

            color:
                var(--admin-heading);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 13px;

        }


        .admin-users-table-card-head p {

            margin:
                4px 0 0;

            color:
                var(--admin-muted);

            font-size:12px;

        }


        .admin-users-table-card-head p strong {

            color:
                var(--admin-heading);

        }


        .admin-users-table-icon {

            width: 36px;

            height: 36px;

            display: grid;

            place-items: center;

            border-radius: 10px;

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

            font-size: 12px;

        }



        /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

        .admin-users-table-wrapper {

            width: 100%;

            overflow-x: auto;

        }


        .admin-users-table {

            width: 100%;

            min-width: 1050px;

            border-collapse: collapse;

        }


        .admin-users-table th {

            padding:
                11px 14px;

            border-bottom:
                1px solid var(--admin-border);

            background:
                var(--admin-surface-soft);

            color:
                var(--admin-muted);

            font-size:12px;

            font-weight: 700;

            letter-spacing: .06em;

            text-align: left;

            text-transform: uppercase;

        }


        .admin-users-table td {

            padding:
                13px 14px;

            border-bottom:
                1px solid var(--admin-border-soft);

            color:
                var(--admin-text);

            font-size: 10px;

            vertical-align: middle;

        }


        .admin-users-table tbody tr:last-child td {

            border-bottom: 0;

        }


        .admin-users-table tbody tr {

            transition:
                background .15s ease;

        }


        .admin-users-table tbody tr:hover {

            background:
                var(--admin-surface-hover);

        }


        .admin-users-actions-heading {

            text-align: right !important;

        }



        /*
    |--------------------------------------------------------------------------
    | User Person
    |--------------------------------------------------------------------------
    */

        .admin-users-person {

            display: flex;

            align-items: center;

            gap: 10px;

            min-width: 175px;

        }


        .admin-users-avatar {

            width: 39px;

            height: 39px;

            flex:
                0 0 39px;

            display: grid;

            place-items: center;

            border-radius: 11px;

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

            font-family:
                'Bricolage Grotesque',
                sans-serif;

            font-size: 13px;

            font-weight: 700;

        }


        .admin-users-person-info {

            min-width: 0;

            display: flex;

            flex-direction: column;

        }


        .admin-users-person-info strong {

            max-width: 175px;

            overflow: hidden;

            color:
                var(--admin-heading);

            font-size: 10px;

            font-weight: 700;

            text-overflow: ellipsis;

            white-space: nowrap;

        }


        .admin-users-person-info span {

            margin-top: 3px;

            color:
                var(--admin-muted);

            font-size:12px;

        }


        .admin-users-person-info small {

            margin-top: 2px;

            color:
                var(--admin-muted-2);

            font-size:12px;

        }



        /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    */

        .admin-users-contact {

            min-width: 180px;

            display: flex;

            flex-direction: column;

            gap: 5px;

        }


        .admin-users-contact span {

            display: flex;

            align-items: center;

            gap: 6px;

            color:
                var(--admin-muted);

            font-size: 12px;

        }


        .admin-users-contact span:first-child {

            color:
                var(--admin-text);

        }


        .admin-users-contact i {

            width: 12px;

            color:
                var(--admin-muted-2);

            font-size:12px;

            text-align: center;

        }



        /*
    |--------------------------------------------------------------------------
    | Role
    |--------------------------------------------------------------------------
    */

        .admin-users-role-badge {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px 9px;

            border-radius: 999px;

            font-size:12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .admin-users-role-badge.seller {

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

        }


        .admin-users-role-badge.buyer {

            background:
                rgba(122, 90, 248, .11);

            color:
                #7a5af8;

        }



        /*
    |--------------------------------------------------------------------------
    | Verification
    |--------------------------------------------------------------------------
    */

        .admin-users-verification {

            display: flex;

            align-items: center;

            gap: 8px;

            min-width: 120px;

        }


        .admin-users-verification>span {

            width: 27px;

            height: 27px;

            flex:
                0 0 27px;

            display: grid;

            place-items: center;

            border-radius: 8px;

            font-size:12px;

        }


        .admin-users-verification>div {

            display: flex;

            flex-direction: column;

        }


        .admin-users-verification strong {

            font-size: 12px;

        }


        .admin-users-verification small {

            margin-top: 2px;

            color:
                var(--admin-muted);

            font-size:12px;

        }


        .admin-users-verification.verified>span {

            background:
                rgba(18, 183, 106, .11);

            color:
                #12b76a;

        }


        .admin-users-verification.verified strong {

            color:
                #12b76a;

        }


        .admin-users-verification.pending>span {

            background:
                rgba(255, 150, 56, .12);

            color:
                #ff9638;

        }


        .admin-users-verification.pending strong {

            color:
                #ff9638;

        }



        /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

        .admin-users-status-badge {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding:
                6px 9px;

            border-radius: 999px;

            font-size:12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .admin-users-status-badge>span {

            width: 6px;

            height: 6px;

            border-radius: 50%;

        }


        .admin-users-status-badge.active {

            background:
                rgba(18, 183, 106, .10);

            color:
                #12b76a;

        }


        .admin-users-status-badge.active>span {

            background:
                #12b76a;

        }


        .admin-users-status-badge.inactive {

            background:
                rgba(239, 83, 80, .10);

            color:
                #ef5350;

        }


        .admin-users-status-badge.inactive>span {

            background:
                #ef5350;

        }



        /*
    |--------------------------------------------------------------------------
    | Last Login
    |--------------------------------------------------------------------------
    */

        .admin-users-last-login {

            min-width: 120px;

            display: flex;

            flex-direction: column;

        }


        .admin-users-last-login strong {

            color:
                var(--admin-heading);

            font-size: 12px;

            font-weight: 600;

        }


        .admin-users-last-login strong.never {

            color:
                var(--admin-muted);

            font-weight: 500;

        }


        .admin-users-last-login span {

            margin-top: 3px;

            color:
                var(--admin-muted);

            font-size:12px;

        }


        .admin-users-last-login small {

            margin-top: 3px;

            color:
                var(--admin-muted-2);

            font-size:12px;

        }


        .admin-users-last-login small i {

            margin-right: 3px;

        }



        /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

        .admin-users-actions {

            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 6px;

            min-width: 160px;

        }


        .admin-users-action-form {

            margin: 0;

        }


        .admin-users-action {

            height: 32px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;

            padding:
                0 9px;

            border:
                1px solid var(--admin-border);

            border-radius: 8px;

            background:
                var(--admin-surface);

            color:
                var(--admin-muted);

            font-family: inherit;

            font-size:12px;

            font-weight: 600;

            white-space: nowrap;

            cursor: pointer;

            transition:
                color .15s ease,
                background .15s ease,
                border-color .15s ease,
                transform .15s ease;

        }


        .admin-users-action:hover {

            transform:
                translateY(-1px);

        }


        .admin-users-action.login:hover {

            border-color:
                var(--admin-accent);

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

        }


        .admin-users-action.deactivate {

            color:
                #ef5350;

        }


        .admin-users-action.deactivate:hover {

            border-color:
                rgba(239, 83, 80, .4);

            background:
                rgba(239, 83, 80, .08);

        }


        .admin-users-action.activate {

            border-color:
                rgba(18, 183, 106, .22);

            color:
                #12b76a;

        }


        .admin-users-action.activate:hover {

            border-color:
                #12b76a;

            background:
                rgba(18, 183, 106, .08);

        }


        .admin-users-action.disabled {

            opacity: .42;

            cursor: not-allowed;

        }


        .admin-users-action.disabled:hover {

            transform: none;

        }



        /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

        .admin-users-empty-cell {

            height: 330px;

        }


        .admin-users-empty {

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 30px;

            text-align: center;

        }


        .admin-users-empty-icon {

            width: 54px;

            height: 54px;

            display: grid;

            place-items: center;

            margin-bottom: 13px;

            border-radius: 15px;

            background:
                var(--admin-accent-soft);

            color:
                var(--admin-accent);

            font-size: 18px;

        }


        .admin-users-empty h3 {

            margin: 0;

            color:
                var(--admin-heading);

            font-size: 12px;

        }


        .admin-users-empty p {

            max-width: 350px;

            margin:
                6px 0 0;

            color:
                var(--admin-muted);

            font-size: 12px;

            line-height: 1.6;

        }


        .admin-users-empty a {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            margin-top: 13px;

            color:
                var(--admin-accent);

            font-size: 12px;

            font-weight: 600;

            text-decoration: none;

        }



        /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

        .admin-users-pagination {

            margin-top: 18px;

        }



        /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

        @media (max-width: 1200px) {

            .admin-users-stat-grid {

                grid-template-columns:
                    repeat(2, minmax(0, 1fr));

            }


            .admin-users-filter-form {

                flex-wrap: wrap;

            }


            .admin-users-search {

                flex-basis:
                    calc(100% - 1px);

            }

        }


        @media (max-width: 800px) {

            .admin-users-page-head {

                flex-direction: column;

                align-items: flex-start;

            }


            .admin-users-header-meta {

                width: 100%;

            }


            .admin-users-header-meta span {

                width: 100%;

                justify-content: center;

            }


            .admin-users-filter-form {

                flex-direction: column;

                align-items: stretch;

            }


            .admin-users-search {

                width: 100%;

                min-width: 0;

            }


            .admin-users-filter-form select {

                width: 100%;

                min-width: 0;

            }


            .admin-users-filter-button {

                width: 100%;

            }


            .admin-users-reset {

                text-align: center;

            }

        }


        @media (max-width: 520px) {

            .admin-users-stat-grid {

                grid-template-columns: 1fr;

            }


            .admin-users-stat-card {

                min-height: 100px;

            }


            .admin-users-page-head h2 {

                font-size: 18px;

            }


            .admin-users-table-card-head {

                padding:
                    13px 14px;

            }

        }
    </style>

@endpush