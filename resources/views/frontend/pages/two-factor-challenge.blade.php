@extends('frontend.layouts.app')


@section(
    'title',
    'Two-Factor Authentication | Midpoint'
)


@section('content')


<section class="tf-login-page">

    <div class="tf-login-card">


        <div class="tf-login-icon">

            <i class="fa-solid fa-shield-halved"></i>

        </div>


        <span class="tf-eyebrow">

            Security verification

        </span>


        <h1>

            Two-factor authentication

        </h1>


        <p>

            Enter the 6-digit code from your authenticator
            app to finish signing in to Midpoint.

            You may also enter one of your recovery codes.

        </p>


        @if($errors->any())

            <div class="tf-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                {{ $errors->first() }}

            </div>

        @endif


        <form
            method="POST"
            action="{{
                route(
                    'two-factor.challenge.store'
                )
            }}"
        >

            @csrf


            <label for="twoFactorLoginCode">

                Authenticator or recovery code

            </label>


            <input
                id="twoFactorLoginCode"

                type="text"

                name="code"

                autocomplete="one-time-code"

                inputmode="text"

                autofocus

                required

                placeholder="000000"
            >


            <button type="submit">

                Verify and continue

                <i class="fa-solid fa-arrow-right"></i>

            </button>

        </form>


        <div class="tf-help">

            <i class="fa-solid fa-lock"></i>

            Don't share your authenticator or recovery
            codes with anyone.

        </div>

    </div>

</section>



@push('styles')

<style>

    .tf-login-page {
        min-height:
            calc(
                100vh - 70px
            );

        display: grid;
        place-items: center;

        padding:
            45px
            16px;

        background: #F6F9F7;
    }


    .tf-login-card {
        width: 100%;
        max-width: 440px;

        padding: 34px;

        border: 1px solid #DCE5E0;
        border-radius: 20px;

        background: #FFFFFF;

        box-shadow:
            0 20px 50px -35px
            rgba(11,61,46,.45);
    }


    .tf-login-icon {
        width: 56px;
        height: 56px;

        display: grid;
        place-items: center;

        margin-bottom: 17px;

        border-radius: 16px;

        background: #E8F7EF;

        color: #087443;

        font-size: 20px;
    }


    .tf-eyebrow {
        color: #12B76A;

        font-size:11px;
        font-weight: 800;

        text-transform: uppercase;

        letter-spacing: .11em;
    }


    .tf-login-card h1 {
        margin:
            6px
            0
            8px;

        color: #101915;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size: 26px;
        font-weight: 800;
    }


    .tf-login-card > p {
        margin:
            0
            0
            20px;

        color: #66736C;

        font-size: 11px;
        line-height: 1.7;
    }


    .tf-login-card label {
        display: block;

        margin-bottom: 7px;

        color: #25322B;

        font-size:12px;
        font-weight: 700;
    }


    .tf-login-card input {
        width: 100%;
        height: 50px;

        padding:
            0
            14px;

        border: 1px solid #DCE5E0;
        border-radius: 11px;

        background: #FFFFFF;

        color: #101915;

        font-size: 16px;
        font-weight: 700;

        letter-spacing: .08em;

        outline: none;
    }


    .tf-login-card input:focus {
        border-color: #12B76A;

        box-shadow:
            0 0 0 3px
            rgba(18,183,106,.1);
    }


    .tf-login-card form button {
        width: 100%;
        height: 47px;

        display: flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        margin-top: 12px;

        border: 0;
        border-radius: 11px;

        background: #0B3D2E;

        color: #FFFFFF;

        font-size: 11px;
        font-weight: 800;

        cursor: pointer;
    }


    .tf-error {
        display: flex;

        gap: 7px;

        margin-bottom: 14px;
        padding: 11px;

        border: 1px solid #FECDD3;
        border-radius: 10px;

        background: #FFF1F2;

        color: #B42318;

        font-size:11px;
    }


    .tf-help {
        display: flex;
        align-items: flex-start;

        gap: 6px;

        margin-top: 15px;

        color: #84908A;

        font-size: 8px;
        line-height: 1.5;
    }


    .tf-help i {
        color: #12B76A;
    }

</style>

@endpush


@endsection