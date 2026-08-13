@extends('admin.layouts.app')

@section('title', 'Role Management')
@section('page-title', 'Role Management')

@section('content')

<div class="arm-page">

    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <div class="arm-head">

        <div>
            <h2>Admin Users & Permissions</h2>

            <p>
                Create administration users and control exactly
                which MidPoint modules they can see and access.
            </p>
        </div>

        <span class="arm-badge">
            <i class="fa-solid fa-shield-halved"></i>
            Super Admin Control
        </span>

    </div>


    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}

    @if(session('success'))

        <div class="arm-alert success">
            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>
        </div>

    @endif


    {{-- =========================================================
        VALIDATION ERRORS
    ========================================================== --}}

    @if($errors->any())

        <div class="arm-alert error">

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
        CREATE USER + INFORMATION
    ========================================================== --}}

    <div class="arm-grid">

        {{-- =====================================================
            CREATE ADMIN USER
        ====================================================== --}}

        <section class="admin-card arm-create-card">

            <div class="arm-card-head">

                <div>
                    <h3>Create Admin User</h3>

                    <p>
                        Create a staff login and select the modules
                        they are allowed to manage.
                    </p>
                </div>

                <span class="arm-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </span>

            </div>


            <form
                method="POST"
                action="{{ route('admin.staff.store') }}"
                class="arm-form"
            >

                @csrf


                {{-- Name + Username --}}

                <div class="arm-fields two">

                    <label>

                        <span>
                            Full name
                        </span>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="120"
                            autocomplete="name"
                            placeholder="e.g. Support Officer"
                        >

                    </label>


                    <label>

                        <span>
                            Username
                        </span>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            maxlength="80"
                            autocomplete="username"
                            placeholder="support.manager"
                        >

                    </label>

                </div>


                {{-- Email --}}

                <label>

                    <span>
                        Email address
                    </span>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="190"
                        autocomplete="email"
                        placeholder="support@example.com"
                    >

                </label>


                {{-- Password --}}

                <div class="arm-fields two">

                    <label>

                        <span>
                            Password
                        </span>

                        <input
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
                        >

                    </label>


                    <label>

                        <span>
                            Confirm password
                        </span>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        >

                    </label>

                </div>


                {{-- =================================================
                    PERMISSIONS
                ================================================== --}}

                <div class="arm-permissions">

                    <div class="arm-permission-title">

                        <div>

                            <strong>
                                Module permissions
                            </strong>

                            <small>
                                Select only the modules this user needs.
                            </small>

                        </div>


                        <button
                            type="button"
                            class="arm-select-all"
                            data-permission-scope="create"
                        >
                            Select all
                        </button>

                    </div>


                    <div
                        class="arm-permission-groups"
                        data-scope="create"
                    >

                        @foreach($permissionGroups as $group => $permissions)

                            <div class="arm-permission-group">

                                <h4>
                                    {{ $group }}
                                </h4>


                                @foreach($permissions as $permission)

                                    <label class="arm-check">

                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission['key'] }}"
                                            {{
                                                in_array(
                                                    $permission['key'],
                                                    old('permissions', []),
                                                    true
                                                )
                                                    ? 'checked'
                                                    : ''
                                            }}
                                        >


                                        <span>
                                            <i class="fa-solid fa-check"></i>
                                        </span>


                                        <em>
                                            {{ $permission['label'] }}
                                        </em>

                                    </label>

                                @endforeach

                            </div>

                        @endforeach

                    </div>

                </div>


                <button
                    class="arm-primary"
                    type="submit"
                >

                    <i class="fa-solid fa-user-shield"></i>

                    Create Admin User

                </button>

            </form>

        </section>


        {{-- =====================================================
            INFORMATION
        ====================================================== --}}

        <section class="admin-card arm-info-card">

            <h3>
                How access works
            </h3>


            <div class="arm-rule">

                <span>1</span>

                <div>

                    <strong>
                        Super Admin
                    </strong>

                    <p>
                        Your main admin account always has unrestricted access.
                    </p>

                </div>

            </div>


            <div class="arm-rule">

                <span>2</span>

                <div>

                    <strong>
                        Restricted Admin
                    </strong>

                    <p>
                        New staff users have the
                        <code>admin_staff</code> role.
                    </p>

                </div>

            </div>


            <div class="arm-rule">

                <span>3</span>

                <div>

                    <strong>
                        Menu + URL Protection
                    </strong>

                    <p>
                        Unauthorized menus are hidden and protected routes
                        return HTTP 403.
                    </p>

                </div>

            </div>


            <div class="arm-rule">

                <span>4</span>

                <div>

                    <strong>
                        Password Control
                    </strong>

                    <p>
                        Change passwords and optionally terminate the user's
                        active login sessions.
                    </p>

                </div>

            </div>


            <div class="arm-rule">

                <span>5</span>

                <div>

                    <strong>
                        Login As User
                    </strong>

                    <p>
                        Login directly as an admin user to verify exactly
                        what that employee can access.
                    </p>

                </div>

            </div>

        </section>

    </div>


    {{-- =========================================================
        ADMINISTRATION USER LIST HEADING
    ========================================================== --}}

    <div class="arm-list-head">

        <div>

            <h3>
                Administration Users
            </h3>

            <p>
                {{ $staff->count() }}
                restricted admin account{{ $staff->count() === 1 ? '' : 's' }}
            </p>

        </div>

    </div>


    {{-- =========================================================
        ADMINISTRATION USERS
    ========================================================== --}}

    <div class="arm-staff-list">

        @forelse($staff as $member)

            @php
                $selected = $member
                    ->adminPermissions
                    ->pluck('permission')
                    ->all();
            @endphp


            <article class="admin-card arm-staff-card">


                {{-- =============================================
                    STAFF ACCOUNT INFO
                ============================================== --}}

                <div class="arm-staff-top">

                    <div class="arm-person">

                        <div class="arm-avatar">

                            {{
                                strtoupper(
                                    mb_substr(
                                        $member->name ?: 'A',
                                        0,
                                        1
                                    )
                                )
                            }}

                        </div>


                        <div>

                            <strong>
                                {{ $member->name }}
                            </strong>


                            <span>

                                @if($member->username)
                                    {{ '@' . $member->username }}
                                    ·
                                @endif

                                {{ $member->email }}

                            </span>


                            <small>

                                Last login:

                                {{
                                    $member->last_login_at
                                        ? $member->last_login_at->diffForHumans()
                                        : 'Never'
                                }}

                            </small>

                        </div>

                    </div>


                    {{-- Status --}}

                    <div
                        class="
                            arm-status
                            {{ $member->status ? 'active' : 'inactive' }}
                        "
                    >

                        <span></span>

                        {{
                            $member->status
                                ? 'Active'
                                : 'Inactive'
                        }}

                    </div>

                </div>


                {{-- =============================================
                    ASSIGNED PERMISSIONS
                ============================================== --}}

                <div class="arm-permission-chips">

                    @forelse($selected as $permission)

                        @php
                            $permissionConfig = config(
                                'admin_permissions.permissions',
                                []
                            );

                            $permissionLabel =
                                $permissionConfig[$permission]['label']
                                ?? $permission;
                        @endphp

                        <span>
                            {{ $permissionLabel }}
                        </span>

                    @empty

                        <span class="none">
                            No module permissions assigned
                        </span>

                    @endforelse

                </div>


                {{-- =============================================
                    QUICK ACTIONS
                ============================================== --}}

                <div class="arm-quick-actions">

                    {{-- Login As Admin User --}}

                    @if($member->status)

                        <form
                            method="POST"
                            action="{{ route('admin.staff.impersonate', $member) }}"
                            onsubmit="return confirm('Login as this admin user?');"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="login"
                            >

                                <i class="fa-solid fa-right-to-bracket"></i>

                                Login

                            </button>

                        </form>

                    @endif


                    {{-- Activate / Deactivate --}}

                    <form
                        method="POST"
                        action="{{ route('admin.staff.status', $member) }}"
                        onsubmit="return confirm('Change this admin user status?');"
                    >

                        @csrf
                        @method('PATCH')


                        <button
                            type="submit"
                            class="{{ $member->status ? 'danger' : 'success' }}"
                        >

                            <i
                                class="
                                    fa-solid
                                    {{
                                        $member->status
                                            ? 'fa-user-slash'
                                            : 'fa-user-check'
                                    }}
                                "
                            ></i>


                            {{
                                $member->status
                                    ? 'Deactivate'
                                    : 'Activate'
                            }}

                        </button>

                    </form>

                </div>


                {{-- =============================================
                    DETAILS
                ============================================== --}}

                <div class="arm-details-grid">


                    {{-- =========================================
                        CHANGE PASSWORD
                    ========================================== --}}

                    <details>

                        <summary>

                            <span>

                                <i class="fa-solid fa-key"></i>

                                Change Password

                            </span>


                            <i class="fa-solid fa-chevron-down"></i>

                        </summary>


                        <form
                            method="POST"
                            action="{{ route('admin.staff.password', $member) }}"
                            class="arm-inner-form"
                        >

                            @csrf
                            @method('PUT')


                            <div class="arm-fields two">

                                <label>

                                    <span>
                                        New password
                                    </span>

                                    <input
                                        type="password"
                                        name="password"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Minimum 8 characters"
                                    >

                                </label>


                                <label>

                                    <span>
                                        Confirm password
                                    </span>

                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        minlength="8"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Confirm password"
                                    >

                                </label>

                            </div>


                            <label class="arm-switch-line">

                                <input
                                    type="checkbox"
                                    name="logout_user"
                                    value="1"
                                    checked
                                >


                                <span>
                                    Log this user out from all active sessions
                                    after changing the password.
                                </span>

                            </label>


                            <button
                                type="submit"
                                class="arm-secondary"
                            >

                                <i class="fa-solid fa-key"></i>

                                Update Password

                            </button>

                        </form>

                    </details>


                    {{-- =========================================
                        EDIT ACCOUNT + PERMISSIONS
                    ========================================== --}}

                    <details>

                        <summary>

                            <span>

                                <i class="fa-solid fa-sliders"></i>

                                Edit Account & Permissions

                            </span>


                            <i class="fa-solid fa-chevron-down"></i>

                        </summary>


                        <form
                            method="POST"
                            action="{{ route('admin.staff.update', $member) }}"
                            class="arm-inner-form"
                        >

                            @csrf
                            @method('PUT')


                            {{-- Name + Username --}}

                            <div class="arm-fields two">

                                <label>

                                    <span>
                                        Full name
                                    </span>

                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $member->name }}"
                                        required
                                        maxlength="120"
                                    >

                                </label>


                                <label>

                                    <span>
                                        Username
                                    </span>

                                    <input
                                        type="text"
                                        name="username"
                                        value="{{ $member->username }}"
                                        required
                                        maxlength="80"
                                    >

                                </label>

                            </div>


                            {{-- Email --}}

                            <label>

                                <span>
                                    Email
                                </span>

                                <input
                                    type="email"
                                    name="email"
                                    value="{{ $member->email }}"
                                    required
                                    maxlength="190"
                                >

                            </label>


                            {{-- Permissions heading --}}

                            <div class="arm-permission-title">

                                <div>

                                    <strong>
                                        Permissions
                                    </strong>

                                    <small>
                                        Update module access for this admin user.
                                    </small>

                                </div>


                                <button
                                    type="button"
                                    class="arm-select-all"
                                    data-permission-scope="staff-{{ $member->id }}"
                                >
                                    Select all
                                </button>

                            </div>


                            {{-- IMPORTANT:
                                Laravel-safe single-line foreach directives
                            --}}

                            <div
                                class="arm-permission-groups compact"
                                data-scope="staff-{{ $member->id }}"
                            >

                                @foreach($permissionGroups as $group => $permissions)

                                    <div class="arm-permission-group">

                                        <h4>
                                            {{ $group }}
                                        </h4>


                                        @foreach($permissions as $permission)

                                            <label class="arm-check">

                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission['key'] }}"
                                                    {{
                                                        in_array(
                                                            $permission['key'],
                                                            $selected,
                                                            true
                                                        )
                                                            ? 'checked'
                                                            : ''
                                                    }}
                                                >


                                                <span>
                                                    <i class="fa-solid fa-check"></i>
                                                </span>


                                                <em>
                                                    {{ $permission['label'] }}
                                                </em>

                                            </label>

                                        @endforeach

                                    </div>

                                @endforeach

                            </div>


                            <button
                                type="submit"
                                class="arm-secondary"
                            >

                                <i class="fa-solid fa-floppy-disk"></i>

                                Save Changes

                            </button>

                        </form>

                    </details>

                </div>


                {{-- =============================================
                    DELETE USER
                ============================================== --}}

                <form
                    method="POST"
                    action="{{ route('admin.staff.destroy', $member) }}"
                    class="arm-delete"
                    onsubmit="return confirm('Permanently delete this admin user? This cannot be undone.');"
                >

                    @csrf
                    @method('DELETE')


                    <button type="submit">

                        <i class="fa-regular fa-trash-can"></i>

                        Delete admin user

                    </button>

                </form>

            </article>


        @empty

            <div class="admin-card arm-empty">

                <i class="fa-solid fa-user-shield"></i>

                <h3>
                    No restricted admin users yet
                </h3>

                <p>
                    Create your first administrator account above.
                </p>

            </div>

        @endforelse

    </div>

</div>

@endsection


@push('styles')

<style>

.arm-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
}


/* =========================================================
   HEADERS
========================================================= */

.arm-head,
.arm-card-head,
.arm-staff-top,
.arm-list-head,
.arm-permission-title,
.arm-quick-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}


.arm-head h2,
.arm-list-head h3,
.arm-card-head h3 {
    margin: 0;
    color: var(--admin-text, #172033);
}


.arm-head p,
.arm-card-head p,
.arm-list-head p {
    margin: 6px 0 0;
    color: var(--admin-muted, #718096);
    font-size: 14px;
}


/* =========================================================
   BADGE
========================================================= */

.arm-badge {
    padding: 9px 12px;
    border-radius: 999px;
    background: #ecfdf5;
    color: #047857;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}


/* =========================================================
   ALERTS
========================================================= */

.arm-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 12px;
    font-size: 14px;
}


.arm-alert.success {
    background: #ecfdf5;
    color: #047857;
}


.arm-alert.error {
    background: #fff1f2;
    color: #be123c;
}


/* =========================================================
   PAGE GRID
========================================================= */

.arm-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
    gap: 20px;
}


.arm-create-card,
.arm-info-card,
.arm-staff-card,
.arm-empty {
    padding: 22px;
}


/* =========================================================
   CREATE ICON
========================================================= */

.arm-icon {
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    flex: 0 0 42px;

    border-radius: 12px;

    background: #ecfdf5;
    color: #047857;
}


/* =========================================================
   FORMS
========================================================= */

.arm-form,
.arm-inner-form {
    display: flex;
    flex-direction: column;
    gap: 15px;

    margin-top: 20px;
}


.arm-fields.two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}


.arm-form label > span,
.arm-inner-form label > span {
    display: block;
    margin-bottom: 7px;

    font-size: 12px;
    font-weight: 700;
    color: #475569;
}


.arm-form input:not([type="checkbox"]),
.arm-inner-form input:not([type="checkbox"]) {
    width: 100%;
    height: 44px;

    border: 1px solid #dbe3ec;
    border-radius: 10px;

    padding: 0 12px;

    background: var(--admin-surface, #ffffff);
    color: var(--admin-text, #172033);

    outline: none;

    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}


.arm-form input:not([type="checkbox"]):focus,
.arm-inner-form input:not([type="checkbox"]):focus {
    border-color: #14a88f;

    box-shadow:
        0 0 0 3px rgba(20, 168, 143, .10);
}


/* =========================================================
   PERMISSIONS
========================================================= */

.arm-permissions {
    border-top: 1px solid #e5e7eb;
    padding-top: 18px;
}


.arm-permission-title small {
    display: block;
    color: #94a3b8;
    margin-top: 3px;
}


.arm-select-all {
    border: 0;

    background: #eff6ff;
    color: #2563eb;

    padding: 7px 10px;

    border-radius: 8px;

    font-size: 12px;
    font-weight: 700;

    cursor: pointer;
}


.arm-select-all:hover {
    background: #dbeafe;
}


.arm-permission-groups {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));

    gap: 14px;

    margin-top: 14px;
}


.arm-permission-groups.compact {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}


.arm-permission-group {
    border: 1px solid #e5e7eb;
    border-radius: 12px;

    padding: 13px;
}


.arm-permission-group h4 {
    margin: 0 0 10px;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: .06em;

    color: #64748b;
}


/* =========================================================
   CUSTOM CHECKBOX
========================================================= */

.arm-check {
    display: flex !important;
    align-items: center;

    gap: 9px;

    margin: 8px 0;

    cursor: pointer;
}


.arm-check input {
    position: absolute;

    opacity: 0;

    pointer-events: none;
}


.arm-check > span {
    width: 20px;
    height: 20px;

    flex: 0 0 20px;

    border: 1px solid #cbd5e1;
    border-radius: 6px;

    display: grid !important;
    place-items: center;

    margin: 0 !important;

    color: transparent;

    background: #ffffff;

    transition: .15s ease;
}


.arm-check input:checked + span {
    background: #0f9f85;
    border-color: #0f9f85;
    color: #ffffff;
}


.arm-check em {
    font-style: normal;
    font-size: 13px;
    color: #334155;
}


/* =========================================================
   BUTTONS
========================================================= */

.arm-primary,
.arm-secondary,
.arm-quick-actions button {
    border: 0;
    border-radius: 10px;

    padding: 11px 14px;

    font-weight: 700;

    cursor: pointer;

    transition:
        opacity .15s ease,
        transform .15s ease;
}


.arm-primary:hover,
.arm-secondary:hover,
.arm-quick-actions button:hover {
    opacity: .9;
}


.arm-primary:active,
.arm-secondary:active,
.arm-quick-actions button:active {
    transform: translateY(1px);
}


.arm-primary {
    background: #0f9f85;
    color: #ffffff;
}


.arm-secondary {
    background: #0f9f85;
    color: #ffffff;

    align-self: flex-start;
}


/* =========================================================
   INFORMATION CARD
========================================================= */

.arm-info-card h3 {
    margin-top: 0;
}


.arm-rule {
    display: flex;

    gap: 12px;

    padding: 13px 0;

    border-bottom: 1px solid #eef2f7;
}


.arm-rule:last-child {
    border-bottom: 0;
}


.arm-rule > span {
    flex: 0 0 30px;

    width: 30px;
    height: 30px;

    border-radius: 9px;

    background: #f1f5f9;

    display: grid;
    place-items: center;

    font-weight: 800;

    color: #0f766e;
}


.arm-rule strong {
    font-size: 14px;
}


.arm-rule p {
    margin: 4px 0 0;

    color: #64748b;

    font-size: 13px;
    line-height: 1.5;
}


/* =========================================================
   STAFF LIST
========================================================= */

.arm-staff-list {
    display: flex;
    flex-direction: column;

    gap: 16px;
}


.arm-person {
    display: flex;
    align-items: center;

    gap: 12px;

    min-width: 0;
}


.arm-avatar {
    width: 44px;
    height: 44px;

    flex: 0 0 44px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #0b684e,
            #18aa84
        );

    display: grid;
    place-items: center;

    color: #ffffff;

    font-weight: 800;
}


.arm-person strong,
.arm-person span,
.arm-person small {
    display: block;
}


.arm-person span {
    font-size: 13px;
    color: #64748b;

    margin-top: 2px;

    word-break: break-word;
}


.arm-person small {
    font-size: 11px;
    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   ACCOUNT STATUS
========================================================= */

.arm-status {
    display: flex;
    align-items: center;

    gap: 7px;

    font-size: 12px;
    font-weight: 700;

    white-space: nowrap;
}


.arm-status > span {
    width: 8px;
    height: 8px;

    border-radius: 50%;
}


.arm-status.active {
    color: #047857;
}


.arm-status.active > span {
    background: #10b981;
}


.arm-status.inactive {
    color: #b91c1c;
}


.arm-status.inactive > span {
    background: #ef4444;
}


/* =========================================================
   PERMISSION CHIPS
========================================================= */

.arm-permission-chips {
    display: flex;
    flex-wrap: wrap;

    gap: 7px;

    margin: 16px 0;
}


.arm-permission-chips span {
    font-size: 11px;

    padding: 6px 8px;

    border-radius: 999px;

    background: #f0fdfa;
    color: #0f766e;
}


.arm-permission-chips span.none {
    background: #f8fafc;
    color: #94a3b8;
}


/* =========================================================
   QUICK ACTIONS
========================================================= */

.arm-quick-actions {
    justify-content: flex-start;
    flex-wrap: wrap;

    border-top: 1px solid #edf2f7;
    border-bottom: 1px solid #edf2f7;

    padding: 13px 0;
}


.arm-quick-actions form {
    margin: 0;
}


.arm-quick-actions button {
    padding: 8px 11px;
}


.arm-quick-actions .login {
    background: #eef2ff;
    color: #4338ca;
}


.arm-quick-actions .danger {
    background: #fff1f2;
    color: #be123c;
}


.arm-quick-actions .success {
    background: #ecfdf5;
    color: #047857;
}


/* =========================================================
   EDIT / PASSWORD DETAILS
========================================================= */

.arm-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;

    gap: 12px;

    margin-top: 14px;
}


.arm-details-grid details {
    border: 1px solid #e5e7eb;

    border-radius: 12px;

    overflow: hidden;

    background: var(--admin-surface, #ffffff);
}


.arm-details-grid summary {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    padding: 13px 14px;

    cursor: pointer;

    font-size: 13px;
    font-weight: 700;

    list-style: none;
}


.arm-details-grid summary::-webkit-details-marker {
    display: none;
}


.arm-details-grid details[open] summary {
    border-bottom: 1px solid #eef2f7;
}


.arm-inner-form {
    padding: 14px;

    margin-top: 0;
}


/* =========================================================
   LOGOUT USER CHECKBOX
========================================================= */

.arm-switch-line {
    display: flex !important;
    align-items: flex-start;

    gap: 9px;

    background: #f8fafc;

    border-radius: 10px;

    padding: 10px;
}


.arm-switch-line input {
    margin-top: 3px;
}


.arm-switch-line span {
    margin: 0 !important;

    font-weight: 500 !important;

    line-height: 1.4;
}


/* =========================================================
   DELETE
========================================================= */

.arm-delete {
    margin-top: 12px;

    text-align: right;
}


.arm-delete button {
    border: 0;

    background: transparent;

    color: #b91c1c;

    font-size: 12px;

    cursor: pointer;
}


.arm-delete button:hover {
    text-decoration: underline;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.arm-empty {
    text-align: center;

    color: #64748b;
}


.arm-empty i {
    font-size: 30px;

    color: #94a3b8;
}


.arm-empty h3 {
    color: #334155;
}


.arm-empty p {
    margin-bottom: 0;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1100px) {

    .arm-grid {
        grid-template-columns: 1fr;
    }


    .arm-permission-groups.compact {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media(max-width: 760px) {

    .arm-head,
    .arm-staff-top {
        align-items: flex-start;
        flex-direction: column;
    }


    .arm-fields.two,
    .arm-permission-groups,
    .arm-permission-groups.compact,
    .arm-details-grid {
        grid-template-columns: 1fr;
    }


    .arm-create-card,
    .arm-info-card,
    .arm-staff-card,
    .arm-empty {
        padding: 16px;
    }


    .arm-badge {
        white-space: normal;
    }

}

</style>

@endpush


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Select All / Clear All Permissions
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.arm-select-all')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const scope =
                    button.dataset.permissionScope;


                const container =
                    document.querySelector(
                        '[data-scope="' + scope + '"]'
                    );


                if (!container) {
                    return;
                }


                const inputs =
                    Array.from(
                        container.querySelectorAll(
                            'input[type="checkbox"]'
                        )
                    );


                if (!inputs.length) {
                    return;
                }


                const allSelected =
                    inputs.every(function (input) {
                        return input.checked;
                    });


                inputs.forEach(function (input) {

                    input.checked =
                        !allSelected;

                });


                button.textContent =
                    allSelected
                        ? 'Select all'
                        : 'Clear all';

            });

        });


    /*
    |--------------------------------------------------------------------------
    | Set Correct Initial Button Text
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.arm-select-all')
        .forEach(function (button) {

            const scope =
                button.dataset.permissionScope;


            const container =
                document.querySelector(
                    '[data-scope="' + scope + '"]'
                );


            if (!container) {
                return;
            }


            const inputs =
                Array.from(
                    container.querySelectorAll(
                        'input[type="checkbox"]'
                    )
                );


            if (!inputs.length) {
                return;
            }


            const allSelected =
                inputs.every(function (input) {
                    return input.checked;
                });


            button.textContent =
                allSelected
                    ? 'Clear all'
                    : 'Select all';

        });

});

</script>

@endpush