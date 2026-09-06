@extends('buyer.layouts.app')


@section('title', 'Open Seller Invite')


@section('content')

<div class="buyer-invite-page">

    <div class="buyer-invite-shell">

        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}

        <div class="buyer-invite-header">

            <div class="buyer-invite-header-icon">
                <i class="fa-solid fa-link"></i>
            </div>


            <div>

                <span class="buyer-invite-eyebrow">
                    Secure transaction
                </span>


                <h1>
                    Open a seller invite
                </h1>


                <p>
                    Already have a Midpoint secure link from a seller?
                    Paste it below to open the transaction directly from your account.
                </p>

            </div>

        </div>


        {{-- =========================================================
            MAIN CARD
        ========================================================== --}}

        <section class="buyer-invite-card">

            <div class="buyer-invite-card-top">

                <div>

                    <h2>
                        Paste your seller invite link
                    </h2>


                    <p>
                        Use the exact secure transaction link the seller sent to you
                        by email, WhatsApp, SMS or another messaging app.
                    </p>

                </div>


                <span class="buyer-invite-secure-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                    Protected
                </span>

            </div>


            {{-- Validation / Session Message --}}

            @if ($errors->has('invite_link'))

                <div
                    class="buyer-invite-alert buyer-invite-alert-error"
                    role="alert"
                >

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <div>

                        <strong>
                            We couldn't open that invite
                        </strong>

                        <span>
                            {{ $errors->first('invite_link') }}
                        </span>

                    </div>

                </div>

            @endif


            @if (session('success'))

                <div
                    class="buyer-invite-alert buyer-invite-alert-success"
                    role="status"
                >

                    <i class="fa-solid fa-circle-check"></i>

                    <div>

                        <strong>
                            Ready
                        </strong>

                        <span>
                            {{ session('success') }}
                        </span>

                    </div>

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('buyer.seller-invite.open') }}"
                id="sellerInviteForm"
                novalidate
            >

                @csrf


                <div class="buyer-invite-field">

                    <label for="inviteLink">
                        Seller invite link
                    </label>


                    <div class="buyer-invite-input-wrap">

                        <span class="buyer-invite-input-icon">
                            <i class="fa-solid fa-link"></i>
                        </span>


                        <input
                            id="inviteLink"
                            type="text"
                            name="invite_link"
                            value="{{ old('invite_link') }}"
                            maxlength="2048"
                            autocomplete="off"
                            autocapitalize="off"
                            spellcheck="false"
                            placeholder="https://midpoint.ng/transaction/..."
                            aria-describedby="inviteLinkHelp inviteLinkClientError"
                            required
                        >


                        <button
                            type="button"
                            id="pasteInviteLink"
                            class="buyer-invite-paste-button"
                        >

                            <i class="fa-regular fa-clipboard"></i>

                            <span>
                                Paste
                            </span>

                        </button>

                    </div>


                    <div class="buyer-invite-field-meta">

                        <span id="inviteLinkHelp">
                            Your seller link usually begins with
                            <strong>{{ url('/transaction') }}/</strong>
                        </span>


                        <span
                            id="inviteLinkClientError"
                            class="buyer-invite-client-error"
                            role="alert"
                            hidden
                        ></span>

                    </div>

                </div>


                <button
                    type="submit"
                    id="openSellerInviteButton"
                    class="buyer-invite-submit"
                >

                    <span class="buyer-invite-submit-normal">

                        <i class="fa-solid fa-arrow-up-right-from-square"></i>

                        Open secure transaction

                    </span>


                    <span
                        class="buyer-invite-submit-loading"
                        hidden
                    >

                        <i class="fa-solid fa-circle-notch fa-spin"></i>

                        Opening invite...

                    </span>

                </button>

            </form>


            {{-- =========================================================
                SECURITY NOTE
            ========================================================== --}}

            <div class="buyer-invite-security-note">

                <div class="buyer-invite-security-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>


                <div>

                    <strong>
                        Your account identity still protects the transaction
                    </strong>


                    <p>
                        Pasting a link does not bypass Midpoint security. The invite
                        can only be opened by the buyer account whose verified email
                        matches the email assigned by the seller.
                    </p>

                </div>

            </div>

        </section>


        {{-- =========================================================
            HOW IT WORKS
        ========================================================== --}}

        <section class="buyer-invite-help-card">

            <div class="buyer-invite-help-heading">

                <div>

                    <span>
                        How it works
                    </span>

                    <h2>
                        From seller link to protected payment
                    </h2>

                </div>


                <i class="fa-solid fa-shield-halved"></i>

            </div>


            <div class="buyer-invite-steps">

                <article>

                    <span class="buyer-invite-step-number">
                        1
                    </span>

                    <div>

                        <strong>
                            Get the seller link
                        </strong>

                        <p>
                            The seller creates a secure Midpoint transaction and
                            shares the invite with your email account.
                        </p>

                    </div>

                </article>


                <article>

                    <span class="buyer-invite-step-number">
                        2
                    </span>

                    <div>

                        <strong>
                            Paste it here
                        </strong>

                        <p>
                            Copy the secure link from the seller's message and paste
                            it above. You do not need to return to your email.
                        </p>

                    </div>

                </article>


                <article>

                    <span class="buyer-invite-step-number">
                        3
                    </span>

                    <div>

                        <strong>
                            Review before paying
                        </strong>

                        <p>
                            Midpoint opens the original transaction so you can verify
                            the seller, item, price and delivery details before payment.
                        </p>

                    </div>

                </article>

            </div>

        </section>


        {{-- =========================================================
            SECONDARY ACTIONS
        ========================================================== --}}

        <div class="buyer-invite-secondary-actions">

            <a
                href="{{ route('buyer.dashboard') }}"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to dashboard
            </a>


            <a
                href="{{ route('buyer.transactions') }}"
            >
                <i class="fa-solid fa-file-lines"></i>
                View my transactions
            </a>


            <a
                href="{{ route('support') }}"
            >
                <i class="fa-regular fa-comments"></i>
                Need help?
            </a>

        </div>

    </div>

</div>


@push('styles')

<style>

/*
|--------------------------------------------------------------------------
| Buyer Seller Invite
|--------------------------------------------------------------------------
*/

.buyer-invite-page {
    width: 100%;
}

.buyer-invite-shell {
    width: min(100%, 900px);
    margin: 0 auto;
}

.buyer-invite-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
}

.buyer-invite-header-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #E8F7EF;
    color: #087443;
    font-size: 17px;
}

.buyer-invite-eyebrow {
    display: block;
    margin-bottom: 3px;
    color: #0E8A54;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.buyer-invite-header h1 {
    margin: 0;
    color: #101915;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.2;
}

.buyer-invite-header p {
    max-width: 620px;
    margin: 6px 0 0;
    color: #68756E;
    font-size: 12px;
    line-height: 1.65;
}

.buyer-invite-card,
.buyer-invite-help-card {
    border: 1px solid #DDE5E1;
    border-radius: 18px;
    background: #FFFFFF;
    box-shadow: 0 16px 42px -34px rgba(11, 61, 46, .35);
}

.buyer-invite-card {
    padding: 26px;
}

.buyer-invite-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
}

.buyer-invite-card-top h2 {
    margin: 0;
    color: #17251F;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 17px;
    font-weight: 800;
}

.buyer-invite-card-top p {
    max-width: 600px;
    margin: 5px 0 0;
    color: #738079;
    font-size: 11px;
    line-height: 1.6;
}

.buyer-invite-secure-badge {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 999px;
    background: #F0FBF5;
    color: #087443;
    font-size: 9px;
    font-weight: 800;
}

.buyer-invite-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 18px;
    padding: 12px 13px;
    border-radius: 11px;
    font-size: 10px;
    line-height: 1.5;
}

.buyer-invite-alert > i {
    margin-top: 2px;
}

.buyer-invite-alert strong,
.buyer-invite-alert span {
    display: block;
}

.buyer-invite-alert span {
    margin-top: 2px;
}

.buyer-invite-alert-error {
    border: 1px solid #FECDD3;
    background: #FFF1F2;
    color: #B42318;
}

.buyer-invite-alert-success {
    border: 1px solid #CDEEDC;
    background: #F2FCF6;
    color: #067647;
}

.buyer-invite-field label {
    display: block;
    margin-bottom: 7px;
    color: #344139;
    font-size: 11px;
    font-weight: 800;
}

.buyer-invite-input-wrap {
    position: relative;
}

.buyer-invite-input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    z-index: 2;
    transform: translateY(-50%);
    color: #12B76A;
    font-size: 13px;
    pointer-events: none;
}

.buyer-invite-input-wrap input {
    width: 100%;
    height: 52px;
    padding: 0 100px 0 42px;
    border: 1.5px solid #DCE5E0;
    border-radius: 12px;
    background: #FFFFFF;
    color: #17251F;
    font-family: inherit;
    font-size: 12px;
    outline: none;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.buyer-invite-input-wrap input::placeholder {
    color: #A0AAA5;
}

.buyer-invite-input-wrap input:focus {
    border-color: #12B76A;
    box-shadow: 0 0 0 3px rgba(18, 183, 106, .09);
}

.buyer-invite-input-wrap input.is-invalid {
    border-color: #E5484D;
    box-shadow: 0 0 0 3px rgba(229, 72, 77, .08);
}

.buyer-invite-paste-button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 0 11px;
    border: 0;
    border-radius: 9px;
    background: #EDF8F2;
    color: #087443;
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    cursor: pointer;
    transition: .15s ease;
}

.buyer-invite-paste-button:hover {
    background: #DFF4E9;
}

.buyer-invite-field-meta {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-top: 7px;
    color: #86918B;
    font-size: 8px;
    line-height: 1.5;
}

.buyer-invite-field-meta strong {
    color: #65716B;
    font-weight: 700;
}

.buyer-invite-client-error {
    color: #B42318;
    font-weight: 700;
    text-align: right;
}

.buyer-invite-submit {
    width: 100%;
    min-height: 47px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 18px;
    padding: 0 18px;
    border: 0;
    border-radius: 11px;
    background: #12B76A;
    color: #FFFFFF;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: transform .15s ease, background .15s ease, box-shadow .15s ease;
}

.buyer-invite-submit:hover {
    background: #0E9F5D;
    box-shadow: 0 10px 24px -16px rgba(14, 159, 93, .6);
    transform: translateY(-1px);
}

.buyer-invite-submit:disabled {
    cursor: wait;
    opacity: .75;
    transform: none;
}

.buyer-invite-submit-normal,
.buyer-invite-submit-loading {
    align-items: center;
    justify-content: center;
    gap: 7px;
}

.buyer-invite-submit-normal {
    display: inline-flex;
}

.buyer-invite-submit-loading:not([hidden]) {
    display: inline-flex;
}

.buyer-invite-security-note {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-top: 20px;
    padding: 14px;
    border: 1px solid #CBE8D8;
    border-radius: 12px;
    background: #F5FCF8;
}

.buyer-invite-security-icon {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    display: grid;
    place-items: center;
    border-radius: 9px;
    background: #E0F5E9;
    color: #087443;
    font-size: 12px;
}

.buyer-invite-security-note strong {
    display: block;
    color: #075F3A;
    font-size: 10px;
}

.buyer-invite-security-note p {
    margin: 4px 0 0;
    color: #587568;
    font-size: 9px;
    line-height: 1.6;
}

.buyer-invite-help-card {
    margin-top: 16px;
    padding: 22px 24px;
}

.buyer-invite-help-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #E8ECEA;
}

.buyer-invite-help-heading span {
    display: block;
    margin-bottom: 2px;
    color: #0E8A54;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .07em;
    text-transform: uppercase;
}

.buyer-invite-help-heading h2 {
    margin: 0;
    color: #17251F;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 14px;
    font-weight: 800;
}

.buyer-invite-help-heading > i {
    color: #12B76A;
    font-size: 16px;
}

.buyer-invite-steps {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 16px;
}

.buyer-invite-steps article {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 12px;
    border-radius: 11px;
    background: #F8FAF9;
}

.buyer-invite-step-number {
    width: 25px;
    height: 25px;
    flex: 0 0 25px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: #E8F7EF;
    color: #087443;
    font-size: 9px;
    font-weight: 800;
}

.buyer-invite-steps strong {
    display: block;
    color: #26342D;
    font-size: 9px;
}

.buyer-invite-steps p {
    margin: 3px 0 0;
    color: #76827C;
    font-size: 8px;
    line-height: 1.55;
}

.buyer-invite-secondary-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 9px;
    margin-top: 16px;
}

.buyer-invite-secondary-actions a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 11px;
    border: 1px solid #DDE5E1;
    border-radius: 9px;
    background: #FFFFFF;
    color: #526059;
    font-size: 9px;
    font-weight: 700;
    text-decoration: none;
    transition: .15s ease;
}

.buyer-invite-secondary-actions a:hover {
    border-color: #BFDCCD;
    background: #F5FBF8;
    color: #087443;
}

@media(max-width: 720px) {

    .buyer-invite-card {
        padding: 20px;
    }

    .buyer-invite-card-top {
        flex-direction: column;
        gap: 10px;
    }

    .buyer-invite-steps {
        grid-template-columns: 1fr;
    }

}

@media(max-width: 520px) {

    .buyer-invite-header {
        gap: 10px;
    }

    .buyer-invite-header-icon {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
    }

    .buyer-invite-header h1 {
        font-size: 20px;
    }

    .buyer-invite-input-wrap input {
        height: 50px;
        padding-right: 48px;
    }

    .buyer-invite-paste-button {
        width: 34px;
        padding: 0;
    }

    .buyer-invite-paste-button span {
        display: none;
    }

    .buyer-invite-field-meta {
        flex-direction: column;
        gap: 4px;
    }

    .buyer-invite-client-error {
        text-align: left;
    }

}

</style>

@endpush


@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'sellerInviteForm'
            );


        const input =
            document.getElementById(
                'inviteLink'
            );


        const pasteButton =
            document.getElementById(
                'pasteInviteLink'
            );


        const submitButton =
            document.getElementById(
                'openSellerInviteButton'
            );


        const normalContent =
            submitButton
                ?.querySelector(
                    '.buyer-invite-submit-normal'
                );


        const loadingContent =
            submitButton
                ?.querySelector(
                    '.buyer-invite-submit-loading'
                );


        const clientError =
            document.getElementById(
                'inviteLinkClientError'
            );


        /*
        |--------------------------------------------------------------------------
        | Client Error
        |--------------------------------------------------------------------------
        */

        function setClientError(
            message = ''
        ) {
            if (
                !input
                ||
                !clientError
            ) {
                return;
            }


            input.classList.toggle(
                'is-invalid',
                message !== ''
            );


            clientError.textContent =
                message;


            clientError.hidden =
                message === '';
        }


        /*
        |--------------------------------------------------------------------------
        | Paste From Clipboard
        |--------------------------------------------------------------------------
        */

        pasteButton
            ?.addEventListener(
                'click',
                async function () {

                    setClientError();


                    if (
                        !navigator.clipboard
                        ||
                        typeof navigator.clipboard.readText !== 'function'
                    ) {
                        input?.focus();

                        setClientError(
                            'Clipboard access is unavailable here. Press Ctrl+V or paste the link manually.'
                        );

                        return;
                    }


                    try {

                        const clipboardText =
                            await navigator.clipboard.readText();


                        if (
                            !clipboardText.trim()
                        ) {
                            input?.focus();

                            setClientError(
                                'Your clipboard is empty. Copy the seller invite first.'
                            );

                            return;
                        }


                        input.value =
                            clipboardText.trim();


                        input.dispatchEvent(
                            new Event(
                                'input',
                                {
                                    bubbles: true,
                                }
                            )
                        );


                        input.focus();

                    } catch (error) {

                        input?.focus();

                        setClientError(
                            'Your browser blocked clipboard access. Press Ctrl+V or paste the link manually.'
                        );
                    }
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Error While Typing
        |--------------------------------------------------------------------------
        */

        input
            ?.addEventListener(
                'input',
                function () {
                    setClientError();
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Submit Protection
        |--------------------------------------------------------------------------
        */

        form
            ?.addEventListener(
                'submit',
                function (event) {

                    const value =
                        input
                            ?.value
                            .trim()
                        ||
                        '';


                    if (
                        value === ''
                    ) {
                        event.preventDefault();

                        setClientError(
                            'Paste the secure seller invite link first.'
                        );

                        input?.focus();

                        return;
                    }


                    setClientError();


                    if (
                        input
                    ) {
                        input.value =
                            value;
                    }


                    if (
                        submitButton
                    ) {
                        submitButton.disabled =
                            true;
                    }


                    if (
                        normalContent
                    ) {
                        normalContent.hidden =
                            true;
                    }


                    if (
                        loadingContent
                    ) {
                        loadingContent.hidden =
                            false;
                    }
                }
            );

    }
);

</script>

@endpush


@endsection
