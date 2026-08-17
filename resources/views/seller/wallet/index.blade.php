@extends('seller.layouts.app')

@section('title', 'Wallet & Withdrawals')


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    .wallet-page {
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
    }


    .wallet-page *,
    .wallet-page *::before,
    .wallet-page *::after {
        box-sizing: border-box;
    }


    .wallet-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }


    .wallet-page-header h1 {
        margin: 0 0 7px;

        color: #10261d;

        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 29px;
        font-weight: 800;
    }


    .wallet-page-header p {
        max-width: 650px;

        margin: 0;

        color: #6b7872;

        font-size: 13px;
        line-height: 1.7;
    }



    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .wallet-alert {
        display: flex;
        align-items: flex-start;
        gap: 9px;

        margin-bottom: 16px;

        padding: 13px 15px;

        border-radius: 13px;

        font-size: 12px;
        font-weight: 600;
        line-height: 1.55;
    }


    .wallet-alert-success {
        border: 1px solid #c8eed9;

        background: #eaf9f1;

        color: #08633b;
    }


    .wallet-alert-error {
        border: 1px solid #ffd2d2;

        background: #fff1f1;

        color: #a42b2b;
    }


    .wallet-alert-info {
        border: 1px solid #e1e8e4;

        background: #f5f8f6;

        color: #52625a;
    }


    .wallet-alert ul {
        margin: 7px 0 0 18px;
        padding: 0;
    }



    /*
    |--------------------------------------------------------------------------
    | Wallet Stats
    |--------------------------------------------------------------------------
    */

    .wallet-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 14px;

        margin-bottom: 20px;
    }


    .wallet-stat {
        padding: 20px;

        border: 1px solid #e1e9e5;
        border-radius: 18px;

        background: #ffffff;

        box-shadow: 0 8px 24px rgba(22, 49, 37, 0.04);
    }


    .wallet-stat-label {
        display: block;

        margin-bottom: 8px;

        color: #79857f;

        font-size: 10px;
        font-weight: 800;

        letter-spacing: 0.07em;
        text-transform: uppercase;
    }


    .wallet-stat-value {
        display: block;

        color: #0b3d2e;

        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 26px;
        font-weight: 800;
    }



    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .wallet-card {
        margin-bottom: 18px;

        padding: 22px;

        border: 1px solid #e1e9e5;
        border-radius: 18px;

        background: #ffffff;

        box-shadow: 0 8px 24px rgba(22, 49, 37, 0.04);
    }


    .wallet-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 18px;

        margin-bottom: 18px;
    }


    .wallet-card-header h2 {
        margin: 0 0 5px;

        color: #17251f;

        font-size: 17px;
        font-weight: 800;
    }


    .wallet-card-header p {
        max-width: 650px;

        margin: 0;

        color: #718078;

        font-size: 12px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    */

    .wallet-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 9px;

        border-radius: 999px;

        white-space: nowrap;

        font-size: 10px;
        font-weight: 800;
    }


    .wallet-badge-success {
        background: #eaf9f1;

        color: #087443;
    }


    .wallet-badge-warning {
        background: #fff6e6;

        color: #a15c00;
    }


    .wallet-badge-danger {
        background: #fff0f0;

        color: #c52c2c;
    }


    .wallet-badge-muted {
        background: #f2f5f3;

        color: #66736d;
    }



    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    */

    .wallet-requirements {
        display: flex;
        flex-wrap: wrap;

        gap: 9px;

        margin-bottom: 20px;
    }


    .wallet-requirement {
        display: inline-flex;
        align-items: center;

        gap: 7px;

        padding: 8px 10px;

        border-radius: 9px;

        background: #f6f8f7;

        color: #627169;

        font-size: 10px;
        font-weight: 700;
    }


    .wallet-requirement.success {
        background: #eaf9f1;

        color: #087443;
    }



    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */

    .wallet-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 14px;
    }


    .wallet-field {
        margin-bottom: 14px;
    }


    .wallet-field-full {
        grid-column: 1 / -1;
    }


    .wallet-field label {
        display: block;

        margin-bottom: 7px;

        color: #35483f;

        font-size: 11px;
        font-weight: 800;
    }


    .wallet-field input,
    .wallet-field select,
    .wallet-field textarea {
        display: block;

        width: 100%;

        padding: 11px 12px;

        border: 1px solid #d9e3de;
        border-radius: 11px;

        outline: none;

        background: #ffffff;

        color: #17251f;

        font-size: 12px;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }


    .wallet-field input:focus,
    .wallet-field select:focus,
    .wallet-field textarea:focus {
        border-color: #26a269;

        box-shadow: 0 0 0 3px rgba(38, 162, 105, 0.10);
    }


    .wallet-field input:disabled,
    .wallet-field select:disabled {
        cursor: not-allowed;

        background: #f4f6f5;

        color: #9aa39e;
    }



    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .wallet-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        min-height: 40px;

        padding: 10px 15px;

        border: 0;
        border-radius: 11px;

        background: #0b3d2e;

        color: #ffffff;

        cursor: pointer;

        font-size: 11px;
        font-weight: 800;

        text-decoration: none;

        transition:
            background 0.15s ease,
            opacity 0.15s ease,
            transform 0.15s ease;
    }


    .wallet-button:hover {
        background: #07543c;
    }


    .wallet-button:active {
        transform: translateY(1px);
    }


    .wallet-button-secondary {
        background: #eef5f1;

        color: #0b3d2e;
    }


    .wallet-button-secondary:hover {
        background: #e1eee7;
    }


    .wallet-button-danger {
        background: #fff0f0;

        color: #c33232;
    }


    .wallet-button-danger:hover {
        background: #ffe3e3;
    }


    .wallet-button:disabled {
        opacity: 0.45;

        cursor: not-allowed;

        transform: none;
    }



    /*
    |--------------------------------------------------------------------------
    | Withdraw Tooltip
    |--------------------------------------------------------------------------
    */

    .wallet-withdraw-button-wrapper {
        position: relative;

        display: inline-flex;
    }


    .wallet-withdraw-tooltip {
        position: absolute;
        z-index: 100;

        left: 50%;
        bottom: calc(100% + 10px);

        width: 285px;

        padding: 12px 13px;

        border-radius: 11px;

        background: #17251f;

        color: #ffffff;

        font-size: 10px;
        line-height: 1.6;

        opacity: 0;
        visibility: hidden;

        pointer-events: none;

        transform: translateX(-50%) translateY(5px);

        transition:
            opacity 0.15s ease,
            visibility 0.15s ease,
            transform 0.15s ease;

        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.20);
    }


    .wallet-withdraw-button-wrapper:hover
    .wallet-withdraw-tooltip,
    .wallet-withdraw-button-wrapper:focus-within
    .wallet-withdraw-tooltip {
        opacity: 1;
        visibility: visible;

        transform: translateX(-50%) translateY(0);
    }


    .wallet-withdraw-tooltip strong {
        display: block;

        margin-bottom: 5px;
    }


    .wallet-tooltip-item {
        display: block;
    }



    /*
    |--------------------------------------------------------------------------
    | Bank Cards
    |--------------------------------------------------------------------------
    */

    .wallet-bank-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 13px;

        margin-bottom: 20px;
    }


    .wallet-bank-card {
        padding: 16px;

        border: 1px solid #dfe7e3;
        border-radius: 15px;

        background: #fbfcfb;
    }


    .wallet-bank-card.active {
        border-color: #85d5ad;

        background: #f2fbf6;
    }


    .wallet-bank-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        gap: 12px;
    }


    .wallet-bank-name {
        margin-bottom: 6px;

        color: #17251f;

        font-size: 13px;
        font-weight: 800;
    }


    .wallet-bank-account-name {
        margin-bottom: 4px;

        color: #5f6e67;

        font-size: 11px;
    }


    .wallet-bank-number {
        color: #0b3d2e;

        font-size: 15px;
        font-weight: 800;

        letter-spacing: 0.08em;
    }


    .wallet-bank-actions {
        display: flex;
        flex-wrap: wrap;

        gap: 8px;

        margin-top: 14px;
    }


    .wallet-bank-actions form {
        margin: 0;
    }



    /*
    |--------------------------------------------------------------------------
    | Resolved Bank
    |--------------------------------------------------------------------------
    */

    .wallet-resolved-account {
        display: none;
        align-items: center;

        gap: 10px;

        margin-bottom: 14px;

        padding: 12px;

        border: 1px solid #bfe8d2;
        border-radius: 11px;

        background: #effaf4;

        color: #0b6842;

        font-size: 11px;
    }


    .wallet-resolved-account.show {
        display: flex;
    }


    .wallet-resolved-account strong {
        display: block;

        margin-bottom: 2px;

        font-size: 12px;
    }



    /*
    |--------------------------------------------------------------------------
    | KYC
    |--------------------------------------------------------------------------
    */

    .wallet-kyc-rejection {
        margin-bottom: 16px;

        padding: 13px;

        border: 1px solid #ffd6d6;
        border-radius: 11px;

        background: #fff5f5;

        color: #a83232;

        font-size: 11px;
        line-height: 1.6;
    }



    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    .wallet-table-wrapper {
        width: 100%;

        overflow-x: auto;
    }


    .wallet-table {
        width: 100%;

        border-collapse: collapse;
    }


    .wallet-table th {
        padding: 10px 9px;

        border-bottom: 1px solid #e3eae6;

        color: #7a8781;

        font-size: 9px;
        font-weight: 800;

        letter-spacing: 0.08em;
        text-align: left;
        text-transform: uppercase;

        white-space: nowrap;
    }


    .wallet-table td {
        padding: 13px 9px;

        border-bottom: 1px solid #eef2f0;

        color: #435149;

        font-size: 11px;
        vertical-align: middle;
    }


    .wallet-table td strong {
        color: #17251f;
    }


    .wallet-empty-state {
        padding: 28px;

        border: 1px dashed #d7e2dc;
        border-radius: 13px;

        color: #748078;

        font-size: 11px;
        text-align: center;
    }



    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    .wallet-modal-backdrop {
        position: fixed;
        z-index: 99999;

        inset: 0;

        display: none;
        align-items: center;
        justify-content: center;

        padding: 20px;

        background: rgba(14, 28, 21, 0.58);
    }


    .wallet-modal-backdrop.show {
        display: flex;
    }


    .wallet-modal {
        width: 100%;
        max-width: 430px;

        padding: 22px;

        border-radius: 18px;

        background: #ffffff;

        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.25);
    }


    .wallet-modal h3 {
        margin: 0 0 8px;

        color: #17251f;

        font-size: 18px;
        font-weight: 800;
    }


    .wallet-modal p {
        margin: 0 0 20px;

        color: #6b7771;

        font-size: 11px;
        line-height: 1.7;
    }


    .wallet-modal-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;

        gap: 9px;
    }



    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 800px) {

        .wallet-stat-grid,
        .wallet-bank-grid,
        .wallet-form-grid {
            grid-template-columns: 1fr;
        }


        .wallet-field-full {
            grid-column: auto;
        }


        .wallet-page-header,
        .wallet-card-header {
            display: block;
        }


        .wallet-card-header .wallet-badge {
            margin-top: 12px;
        }


        .wallet-stat-value {
            font-size: 23px;
        }

    }

</style>

@endpush



@section('content')

<div class="wallet-page">


    {{-- ============================================================
        PAGE HEADER
    ============================================================= --}}

    <div class="wallet-page-header">

        <div>

            <h1>
                Wallet & withdrawals
            </h1>


            <p>
                Released transaction funds collect in your Midpoint wallet.
                Add a verified withdrawal bank account and complete KYC before
                withdrawing your available funds.
            </p>

        </div>

    </div>



    {{-- ============================================================
        FLASH MESSAGES
    ============================================================= --}}

    @if(session('success'))

        <div class="wallet-alert wallet-alert-success">

            <i class="fa-solid fa-circle-check"></i>


            <div>
                {{ session('success') }}
            </div>

        </div>

    @endif


    @if(session('error'))

        <div class="wallet-alert wallet-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>
                {{ session('error') }}
            </div>

        </div>

    @endif



    {{-- ============================================================
        VALIDATION ERRORS
    ============================================================= --}}

    @if($errors->any())

        <div class="wallet-alert wallet-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>


            <div>

                <strong>
                    Please fix the following:
                </strong>


                <ul>

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif



    {{-- ============================================================
        WALLET BALANCES
    ============================================================= --}}

    <div class="wallet-stat-grid">


        <div class="wallet-stat">

            <span class="wallet-stat-label">
                Available to withdraw
            </span>


            <strong class="wallet-stat-value">

                ₦{{ number_format((float) $wallet->available_balance, 2) }}

            </strong>

        </div>



        <div class="wallet-stat">

            <span class="wallet-stat-label">
                Pending withdrawal
            </span>


            <strong class="wallet-stat-value">

                ₦{{ number_format((float) $wallet->pending_withdrawal_balance, 2) }}

            </strong>

        </div>



        <div class="wallet-stat">

            <span class="wallet-stat-label">
                Total withdrawn
            </span>


            <strong class="wallet-stat-value">

                ₦{{ number_format((float) $wallet->total_withdrawn, 2) }}

            </strong>

        </div>

    </div>



    {{-- ============================================================
        WITHDRAW FUNDS
    ============================================================= --}}

    <section class="wallet-card">

        <div class="wallet-card-header">

            <div>

                <h2>
                    Withdraw funds
                </h2>


                <p>
                    Withdrawals can only be sent to your active verified
                    bank account after your seller KYC has been approved.
                </p>

            </div>


            @if(count($withdrawalBlockers) === 0)

                <span class="wallet-badge wallet-badge-success">

                    <i class="fa-solid fa-lock-open"></i>

                    Ready to withdraw

                </span>

            @else

                <span class="wallet-badge wallet-badge-warning">

                    <i class="fa-solid fa-lock"></i>

                    Withdrawal locked

                </span>

            @endif

        </div>



        {{-- Requirements --}}

        <div class="wallet-requirements">


            <span
                class="
                    wallet-requirement
                    {{ $activeAccount && $activeAccount->is_verified ? 'success' : '' }}
                "
            >

                <i
                    class="
                        fa-solid
                        {{ $activeAccount && $activeAccount->is_verified ? 'fa-circle-check' : 'fa-circle-xmark' }}
                    "
                ></i>


                Verified active bank

            </span>



            <span
                class="
                    wallet-requirement
                    {{ $kycApproved ? 'success' : '' }}
                "
            >

                <i
                    class="
                        fa-solid
                        {{ $kycApproved ? 'fa-circle-check' : 'fa-circle-xmark' }}
                    "
                ></i>


                KYC verified

            </span>



            <span
                class="
                    wallet-requirement
                    {{ (float) $wallet->available_balance >= (float) $minimumWithdrawal ? 'success' : '' }}
                "
            >

                <i
                    class="
                        fa-solid
                        {{ (float) $wallet->available_balance >= (float) $minimumWithdrawal ? 'fa-circle-check' : 'fa-circle-xmark' }}
                    "
                ></i>


                Minimum
                ₦{{ number_format((float) $minimumWithdrawal, 0) }}

            </span>

        </div>



        <form
            method="POST"
            action="{{ route('seller.wallet.withdraw') }}"
        >

            @csrf


            <div
                class="wallet-field"
                style="max-width: 400px;"
            >

                <label for="withdrawAmount">
                    Amount to withdraw (₦)
                </label>


                <input
                    id="withdrawAmount"
                    type="number"
                    name="amount"
                    value="{{ old('amount') }}"
                    min="{{ $minimumWithdrawal }}"
                    max="{{ (float) $wallet->available_balance }}"
                    step="0.01"
                    placeholder="e.g. 25000"
                    {{ count($withdrawalBlockers) === 0 ? '' : 'disabled' }}
                    required
                >

            </div>



            @if($activeAccount)

                <div
                    class="wallet-alert wallet-alert-info"
                    style="max-width: 560px;"
                >

                    <i class="fa-solid fa-building-columns"></i>


                    <div>

                        Withdrawal destination:

                        <strong>
                            {{ $activeAccount->bank_name }}
                        </strong>

                        ·

                        {{ $activeAccount->account_name }}

                        ·

                        ••••{{ $activeAccount->account_number_last4 }}

                    </div>

                </div>

            @endif



            @if(count($withdrawalBlockers) === 0)

                <button
                    type="submit"
                    class="wallet-button"
                >

                    <i class="fa-solid fa-money-bill-transfer"></i>

                    Withdraw funds

                </button>

            @else

                <span
                    class="wallet-withdraw-button-wrapper"
                    tabindex="0"
                >

                    <button
                        type="button"
                        class="wallet-button"
                        disabled
                    >

                        <i class="fa-solid fa-lock"></i>

                        Withdraw funds

                    </button>


                    <span class="wallet-withdraw-tooltip">

                        <strong>
                            Withdrawal unavailable
                        </strong>


                        @foreach($withdrawalBlockers as $blocker)

                            <span class="wallet-tooltip-item">

                                • {{ $blocker }}

                            </span>

                        @endforeach

                    </span>

                </span>

            @endif

        </form>

    </section>



    {{-- ============================================================
        BANK ACCOUNTS
    ============================================================= --}}

    <section
        id="bank-accounts"
        class="wallet-card"
    >

        <div class="wallet-card-header">

            <div>

                <h2>
                    Withdrawal bank accounts
                </h2>


                <p>
                    Add up to two verified bank accounts.
                    Your first account becomes active automatically.
                    Verified bank information cannot be edited.
                    Delete an account if you need to replace it.
                </p>

            </div>


            <span
                class="
                    wallet-badge
                    {{ $accounts->count() >= 2 ? 'wallet-badge-warning' : 'wallet-badge-muted' }}
                "
            >

                {{ $accounts->count() }}/2 added

            </span>

        </div>



        {{-- Existing Accounts --}}

        @if($accounts->isNotEmpty())

            <div class="wallet-bank-grid">

                @foreach($accounts as $account)

                    <div
                        class="
                            wallet-bank-card
                            {{ $account->is_active ? 'active' : '' }}
                        "
                    >

                        <div class="wallet-bank-top">

                            <div>

                                <div class="wallet-bank-name">
                                    {{ $account->bank_name }}
                                </div>


                                <div class="wallet-bank-account-name">
                                    {{ $account->account_name }}
                                </div>


                                <div class="wallet-bank-number">

                                    •••• {{ $account->account_number_last4 }}

                                </div>

                            </div>


                            <div>

                                @if($account->is_active)

                                    <span class="wallet-badge wallet-badge-success">

                                        <i class="fa-solid fa-circle-check"></i>

                                        Active

                                    </span>

                                @else

                                    <span class="wallet-badge wallet-badge-muted">

                                        <i class="fa-solid fa-shield-halved"></i>

                                        Verified

                                    </span>

                                @endif

                            </div>

                        </div>



                        <div class="wallet-bank-actions">


                            @if(!$account->is_active)

                                <form
                                    method="POST"
                                    action="{{ route('seller.wallet.banks.activate', $account) }}"
                                >

                                    @csrf
                                    @method('PATCH')


                                    <button
                                        type="submit"
                                        class="wallet-button wallet-button-secondary"
                                    >

                                        <i class="fa-solid fa-circle-check"></i>

                                        Make active

                                    </button>

                                </form>

                            @endif



                            <button
                                type="button"
                                class="wallet-button wallet-button-danger js-delete-bank"
                                data-action="{{ route('seller.wallet.banks.destroy', $account) }}"
                                data-bank="{{ $account->bank_name }} ••••{{ $account->account_number_last4 }}"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Delete

                            </button>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif



        {{-- Add Bank Account --}}

        @if($accounts->count() < 2)

            @if($bankLoadError)

                <div class="wallet-alert wallet-alert-error">

                    <i class="fa-solid fa-circle-exclamation"></i>


                    <div>
                        {{ $bankLoadError }}
                    </div>

                </div>

            @else

                <form
                    method="POST"
                    action="{{ route('seller.wallet.banks.store') }}"
                    id="bankAddForm"
                >

                    @csrf


                    <div class="wallet-form-grid">


                        <div class="wallet-field">

                            <label for="bankCode">
                                Bank
                            </label>


                            <select
                                id="bankCode"
                                name="bank_code"
                                required
                            >

                                <option value="">
                                    Select bank
                                </option>


                                @foreach($banks as $bank)

                                    <option
                                        value="{{ $bank['code'] }}"
                                        {{ old('bank_code') == $bank['code'] ? 'selected' : '' }}
                                    >

                                        {{ $bank['name'] }}

                                    </option>

                                @endforeach

                            </select>

                        </div>



                        <div class="wallet-field">

                            <label for="accountNumber">
                                10-digit account number
                            </label>


                            <input
                                id="accountNumber"
                                type="text"
                                name="account_number"
                                value="{{ old('account_number') }}"
                                inputmode="numeric"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                autocomplete="off"
                                placeholder="0123456789"
                                required
                            >

                        </div>

                    </div>



                    {{-- Resolved Account --}}

                    <div
                        id="resolvedAccount"
                        class="wallet-resolved-account"
                    >

                        <i class="fa-solid fa-circle-check"></i>


                        <div>

                            <strong id="resolvedName">
                                Account verified
                            </strong>


                            <span id="resolvedBank"></span>

                        </div>

                    </div>



                    <div
                        style="
                            display: flex;
                            flex-wrap: wrap;
                            gap: 9px;
                        "
                    >

                        <button
                            type="button"
                            class="wallet-button wallet-button-secondary"
                            id="verifyBankButton"
                        >

                            <i class="fa-solid fa-shield-halved"></i>

                            Verify account

                        </button>



                        <button
                            type="submit"
                            class="wallet-button"
                            id="addBankButton"
                            disabled
                        >

                            <i class="fa-solid fa-plus"></i>

                            Verify & add account

                        </button>

                    </div>

                </form>

            @endif

        @else

            <div class="wallet-alert wallet-alert-info">

                <i class="fa-solid fa-circle-info"></i>


                <div>

                    You already have the maximum of
                    <strong>2 withdrawal accounts</strong>.

                    Delete one of your existing accounts before adding another.

                </div>

            </div>

        @endif

    </section>



    {{-- ============================================================
        KYC
    ============================================================= --}}

    @include(
        'seller.wallet.partials.automated-kyc'
    )



        {{-- ============================================================
            WITHDRAWAL HISTORY
        ============================================================= --}}

        <section class="wallet-card">

            <div class="wallet-card-header">

                <div>

                    <h2>
                        Withdrawal history
                    </h2>


                    <p>
                        Review every payout request and its current status.
                    </p>

                </div>

            </div>



            @if($withdrawals->count() > 0)

                <div class="wallet-table-wrapper">

                    <table class="wallet-table">

                        <thead>

                            <tr>

                                <th>
                                    Reference
                                </th>

                                <th>
                                    Bank
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Requested
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($withdrawals as $withdrawal)

                                @php

                                    $withdrawalStatusClass = 'wallet-badge-warning';

                                    if (
                                        $withdrawal->status ===
                                        \App\Models\SellerWithdrawal::STATUS_SUCCESSFUL
                                    ) {
                                        $withdrawalStatusClass = 'wallet-badge-success';
                                    }

                                    if (
                                        $withdrawal->status ===
                                        \App\Models\SellerWithdrawal::STATUS_FAILED
                                        ||
                                        $withdrawal->status ===
                                        \App\Models\SellerWithdrawal::STATUS_REVERSED
                                    ) {
                                        $withdrawalStatusClass = 'wallet-badge-danger';
                                    }

                                @endphp


                                <tr>

                                    <td>

                                        <strong>
                                            {{ $withdrawal->reference }}
                                        </strong>

                                    </td>


                                    <td>

                                        {{ $withdrawal->bank_name }}


                                        <br>


                                        <small>
                                            ••••{{ $withdrawal->account_number_last4 }}
                                        </small>

                                    </td>


                                    <td>

                                        <strong>

                                            ₦{{ number_format((float) $withdrawal->amount, 2) }}

                                        </strong>

                                    </td>


                                    <td>

                                        <span
                                            class="
                                                wallet-badge
                                                {{ $withdrawalStatusClass }}
                                            "
                                        >

                                            {{ ucfirst(str_replace('_', ' ', $withdrawal->status)) }}

                                        </span>

                                    </td>


                                    <td>

                                        @if($withdrawal->requested_at)

                                            {{ $withdrawal->requested_at->format('d M Y, h:i A') }}

                                        @else

                                            —

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>



                <div style="margin-top: 16px;">

                    {{ $withdrawals->links() }}

                </div>

            @else

                <div class="wallet-empty-state">

                    <i
                        class="fa-solid fa-money-bill-transfer"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-size: 22px;
                        "
                    ></i>


                    You have not requested any withdrawals yet.

                </div>

            @endif

        </section>



        {{-- ============================================================
            WALLET ACTIVITY
        ============================================================= --}}

        <section class="wallet-card">

            <div class="wallet-card-header">

                <div>

                    <h2>
                        Wallet activity
                    </h2>


                    <p>
                        Recent credits, withdrawal reservations and refunds.
                    </p>

                </div>

            </div>



            @if($ledger->count() > 0)

                <div class="wallet-table-wrapper">

                    <table class="wallet-table">

                        <thead>

                            <tr>

                                <th>
                                    Activity
                                </th>

                                <th>
                                    Reference
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Date
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($ledger as $entry)

                                <tr>

                                    <td>

                                        <strong>

                                            {{ ucfirst(str_replace('_', ' ', $entry->type)) }}

                                        </strong>


                                        @if($entry->description)

                                            <br>


                                            <small>
                                                {{ $entry->description }}
                                            </small>

                                        @endif

                                    </td>


                                    <td>
                                        {{ $entry->reference }}
                                    </td>


                                    <td>

                                        <strong>

                                            @if($entry->direction === 'credit')
                                                +
                                            @else
                                                -
                                            @endif

                                            ₦{{ number_format((float) $entry->amount, 2) }}

                                        </strong>

                                    </td>


                                    <td>

                                        @php

                                            $ledgerBadgeClass = 'wallet-badge-muted';

                                            if ($entry->status === 'posted') {
                                                $ledgerBadgeClass = 'wallet-badge-success';
                                            }

                                            if ($entry->status === 'pending') {
                                                $ledgerBadgeClass = 'wallet-badge-warning';
                                            }

                                            if ($entry->status === 'failed') {
                                                $ledgerBadgeClass = 'wallet-badge-danger';
                                            }

                                        @endphp


                                        <span
                                            class="
                                                wallet-badge
                                                {{ $ledgerBadgeClass }}
                                            "
                                        >

                                            {{ ucfirst($entry->status) }}

                                        </span>

                                    </td>


                                    <td>

                                        {{ $entry->created_at->format('d M Y, h:i A') }}

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="wallet-empty-state">

                    No wallet activity yet.

                </div>

            @endif

        </section>

    </div>



{{-- ================================================================
    DELETE BANK CONFIRMATION MODAL
================================================================= --}}

<div
    id="deleteBankModal"
    class="wallet-modal-backdrop"
    aria-hidden="true"
>

    <div
        class="wallet-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteBankModalTitle"
    >

        <h3 id="deleteBankModalTitle">
            Delete withdrawal account?
        </h3>


        <p id="deleteBankModalText">

            This verified bank account will be permanently removed.

        </p>



        <div class="wallet-modal-actions">

            <button
                type="button"
                class="wallet-button wallet-button-secondary"
                id="cancelDeleteBank"
            >

                Cancel

            </button>



            <form
                method="POST"
                id="deleteBankForm"
                action=""
            >

                @csrf
                @method('DELETE')


                <button
                    type="submit"
                    class="wallet-button wallet-button-danger"
                >

                    <i class="fa-solid fa-trash"></i>

                    Yes, delete

                </button>

            </form>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            |--------------------------------------------------------------------------
            | Bank Verification
            |--------------------------------------------------------------------------
            */

            const bankCode =
                document.getElementById(
                    'bankCode'
                );


            const accountNumber =
                document.getElementById(
                    'accountNumber'
                );


            const verifyBankButton =
                document.getElementById(
                    'verifyBankButton'
                );


            const addBankButton =
                document.getElementById(
                    'addBankButton'
                );


            const resolvedAccount =
                document.getElementById(
                    'resolvedAccount'
                );


            const resolvedName =
                document.getElementById(
                    'resolvedName'
                );


            const resolvedBank =
                document.getElementById(
                    'resolvedBank'
                );


            /*
            |--------------------------------------------------------------------------
            | Reset Verification
            |--------------------------------------------------------------------------
            */

            function resetBankVerification() {

                if (
                    resolvedAccount
                ) {

                    resolvedAccount
                        .classList
                        .remove(
                            'show'
                        );
                }


                if (
                    addBankButton
                ) {

                    addBankButton.disabled =
                        true;
                }
            }



            /*
            |--------------------------------------------------------------------------
            | Bank Changed
            |--------------------------------------------------------------------------
            */

            if (
                bankCode
            ) {

                bankCode.addEventListener(
                    'change',
                    function () {

                        resetBankVerification();
                    }
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Account Number Input
            |--------------------------------------------------------------------------
            */

            if (
                accountNumber
            ) {

                accountNumber.addEventListener(
                    'input',
                    function () {

                        this.value =
                            this.value
                                .replace(
                                    /\D/g,
                                    ''
                                )
                                .slice(
                                    0,
                                    10
                                );


                        resetBankVerification();
                    }
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Verify Bank
            |--------------------------------------------------------------------------
            */

            if (
                verifyBankButton
            ) {

                verifyBankButton.addEventListener(
                    'click',
                    async function () {

                        if (
                            !bankCode
                            ||
                            !bankCode.value
                        ) {

                            alert(
                                'Please select your bank.'
                            );

                            return;
                        }


                        if (
                            !accountNumber
                            ||
                            !/^\d{10}$/.test(
                                accountNumber.value
                            )
                        ) {

                            alert(
                                'Please enter a valid 10-digit account number.'
                            );

                            return;
                        }


                        const originalContent =
                            verifyBankButton.innerHTML;


                        verifyBankButton.disabled =
                            true;


                        verifyBankButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';


                        resetBankVerification();


                        try {

                            const response =
                                await fetch(
                                    '{{ route('seller.wallet.banks.resolve') }}',
                                    {
                                        method: 'POST',

                                        headers: {
                                            'Accept':
                                                'application/json',

                                            'Content-Type':
                                                'application/json',

                                            'X-CSRF-TOKEN':
                                                '{{ csrf_token() }}'
                                        },

                                        credentials:
                                            'same-origin',

                                        body:
                                            JSON.stringify({
                                                bank_code:
                                                    bankCode.value,

                                                account_number:
                                                    accountNumber.value
                                            })
                                    }
                                );


                            let responseData =
                                {};


                            try {

                                responseData =
                                    await response.json();

                            } catch (
                                jsonError
                            ) {

                                responseData =
                                    {};
                            }


                            if (
                                !response.ok
                            ) {

                                let errorMessage =
                                    responseData.message
                                    ||
                                    'Unable to verify this bank account.';


                                if (
                                    responseData.errors
                                ) {

                                    const errors =
                                        Object
                                            .values(
                                                responseData.errors
                                            )
                                            .flat();


                                    if (
                                        errors.length > 0
                                    ) {

                                        errorMessage =
                                            errors[0];
                                    }
                                }


                                throw new Error(
                                    errorMessage
                                );
                            }


                            if (
                                !responseData.account_name
                            ) {

                                throw new Error(
                                    'The account was not resolved successfully.'
                                );
                            }


                            if (
                                resolvedName
                            ) {

                                resolvedName.textContent =
                                    responseData.account_name;
                            }


                            if (
                                resolvedBank
                            ) {

                                resolvedBank.textContent =
                                    responseData.bank_name
                                    +
                                    ' • '
                                    +
                                    responseData.account_number;
                            }


                            if (
                                resolvedAccount
                            ) {

                                resolvedAccount
                                    .classList
                                    .add(
                                        'show'
                                    );
                            }


                            if (
                                addBankButton
                            ) {

                                addBankButton.disabled =
                                    false;
                            }

                        } catch (
                            error
                        ) {

                            resetBankVerification();


                            alert(
                                error.message
                                ||
                                'Unable to verify this bank account.'
                            );

                        } finally {

                            verifyBankButton.disabled =
                                false;


                            verifyBankButton.innerHTML =
                                originalContent;
                        }
                    }
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Delete Bank Confirmation
            |--------------------------------------------------------------------------
            */

            const deleteBankModal =
                document.getElementById(
                    'deleteBankModal'
                );


            const deleteBankForm =
                document.getElementById(
                    'deleteBankForm'
                );


            const deleteBankModalText =
                document.getElementById(
                    'deleteBankModalText'
                );


            const cancelDeleteBank =
                document.getElementById(
                    'cancelDeleteBank'
                );


            const deleteButtons =
                document.querySelectorAll(
                    '.js-delete-bank'
                );


            function closeDeleteModal() {

                if (
                    !deleteBankModal
                ) {
                    return;
                }


                deleteBankModal
                    .classList
                    .remove(
                        'show'
                    );


                deleteBankModal.setAttribute(
                    'aria-hidden',
                    'true'
                );
            }



            deleteButtons.forEach(
                function (
                    button
                ) {

                    button.addEventListener(
                        'click',
                        function () {

                            if (
                                !deleteBankModal
                                ||
                                !deleteBankForm
                            ) {
                                return;
                            }


                            deleteBankForm.action =
                                this.dataset.action;


                            if (
                                deleteBankModalText
                            ) {

                                deleteBankModalText.textContent =
                                    'Delete '
                                    +
                                    this.dataset.bank
                                    +
                                    '? This verified account cannot be restored. '
                                    +
                                    'You can add another bank account later, '
                                    +
                                    'up to the maximum of 2 accounts.';
                            }


                            deleteBankModal
                                .classList
                                .add(
                                    'show'
                                );


                            deleteBankModal.setAttribute(
                                'aria-hidden',
                                'false'
                            );
                        }
                    );
                }
            );



            if (
                cancelDeleteBank
            ) {

                cancelDeleteBank.addEventListener(
                    'click',
                    function () {

                        closeDeleteModal();
                    }
                );
            }



            if (
                deleteBankModal
            ) {

                deleteBankModal.addEventListener(
                    'click',
                    function (
                        event
                    ) {

                        if (
                            event.target ===
                            deleteBankModal
                        ) {

                            closeDeleteModal();
                        }
                    }
                );
            }



            /*
            |--------------------------------------------------------------------------
            | ESC Closes Modal
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                function (
                    event
                ) {

                    if (
                        event.key ===
                        'Escape'
                    ) {

                        closeDeleteModal();
                    }
                }
            );

        }
    );

</script>

@endpush