{{-- ================================================================
PAYSTACK IDENTITY VERIFICATION
================================================================= --}}

@php

    /*
    |--------------------------------------------------------------------------
    | Is Existing KYC Valid For Current Active Bank?
    |--------------------------------------------------------------------------
    */

    $kycMatchesActiveBank =

        $kyc

        &&

        $kyc
            ->isApprovedForWithdrawalAccount(
                $activeAccount
            );


    /*
    |--------------------------------------------------------------------------
    | Fully Verified
    |--------------------------------------------------------------------------
    */

    $kycFullyVerified =

        $kyc

        &&

        $kycMatchesActiveBank;


    /*
    |--------------------------------------------------------------------------
    | Processing For Current Bank
    |--------------------------------------------------------------------------
    */

    $kycProcessing =

        $kyc

        &&

        $kyc->status
        ===
        \App\Models\SellerKycVerification::STATUS_PROCESSING

        &&

        $activeAccount

        &&

        (int) 
        $kyc
            ->seller_withdrawal_account_id

        ===

        (int) 
        $activeAccount
            ->id;


    $kycWasReused =
        $kycFullyVerified
        &&
        !empty(
        $kyc->reused_from_kyc_id
    );


    /*
    |--------------------------------------------------------------------------
    | Prefill Seller Name
    |--------------------------------------------------------------------------
    */

    $sourceName =
        trim(
            $kyc
            ? $kyc->legal_name
            : $seller->name
        );


    $nameParts =
        preg_split(
            '/\s+/',
            $sourceName,
            -1,
            PREG_SPLIT_NO_EMPTY
        )
        ?:
        [];


    $defaultFirstName =
        $nameParts[0]
        ??
        '';


    $defaultLastName =
        count(
            $nameParts
        )
        >
        1

        ? $nameParts[
            count(
                $nameParts
            )
            -
            1
        ]

        : '';


    $defaultMiddleName =

        count(
            $nameParts
        )
        >
        2

        ? implode(
            ' ',
            array_slice(
                $nameParts,
                1,
                -1
            )
        )

        : '';

@endphp


<section id="kyc" class="wallet-card">

    {{-- ============================================================
    HEADER
    ============================================================= --}}

    <div class="wallet-card-header">

        <div>

            <h2>
                Identity verification
            </h2>


            <p>

                Verify your Nigerian identity through Paystack
                using your BVN and your active verified withdrawal
                bank account.

                No selfie upload or manual admin approval is required.

            </p>

        </div>


        @if($kycFullyVerified)

            <span class="wallet-badge wallet-badge-success">

                <i class="fa-solid fa-circle-check"></i>

                Verified

            </span>


        @elseif($kycProcessing)

            <span class="wallet-badge wallet-badge-warning">

                <i class="fa-solid fa-spinner fa-spin"></i>

                Verifying

            </span>


        @elseif(
                $kyc
                &&
                $kyc->status
                ===
                \App\Models\SellerKycVerification::STATUS_REJECTED
            )

            <span class="wallet-badge wallet-badge-danger">

                <i class="fa-solid fa-circle-xmark"></i>

                Verification failed

            </span>


        @elseif(
                $kyc
                &&
                $kyc->status
                ===
                \App\Models\SellerKycVerification::STATUS_PROVIDER_ERROR
            )

            <span class="wallet-badge wallet-badge-warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                Retry required

            </span>


        @else

            <span class="wallet-badge wallet-badge-muted">

                Not verified

            </span>

        @endif

    </div>



    {{-- ============================================================
    SUCCESS
    ============================================================= --}}

    @if($kycFullyVerified)

        <div class="wallet-alert wallet-alert-success">

            <i class="fa-solid fa-shield-halved"></i>


            <div>

                <strong>
                    @if($kycWasReused)
                        Existing verified identity securely matched
                    @else
                        Identity and bank ownership verified by Paystack
                    @endif
                </strong>


                <br>


                @if($kycWasReused)
                    Your submitted identity and active bank details matched
                    a previous successful Paystack verification.
                @else
                    Your BVN was successfully validated against your
                    active withdrawal bank account.
                @endif

                Withdrawals are now unlocked.

            </div>

        </div>



        <div style="
                    display:grid;
                    grid-template-columns:
                        repeat(
                            auto-fit,
                            minmax(170px,1fr)
                        );
                    gap:10px;
                    margin-top:15px;
                ">


            {{-- Verified identity --}}

            <div style="
                        padding:12px;
                        background:#f7faf8;
                        border:1px solid #e4ebe7;
                        border-radius:11px;
                    ">

                <small style="
                            display:block;
                            color:#7a8781;
                            margin-bottom:5px;
                        ">
                    Verified identity
                </small>


                <strong style="
                            font-size:11px;
                            color:#183529;
                        ">

                    {{ $kyc->verified_full_name }}

                </strong>

            </div>



            {{-- BVN --}}

            <div style="
                        padding:12px;
                        background:#f7faf8;
                        border:1px solid #e4ebe7;
                        border-radius:11px;
                    ">

                <small style="
                            display:block;
                            color:#7a8781;
                            margin-bottom:5px;
                        ">
                    BVN
                </small>


                <strong style="
                            font-size:11px;
                            color:#183529;
                        ">

                    •••••••{{ $kyc->id_number_last4 }}

                </strong>

            </div>



            {{-- Verified Bank --}}

            <div style="
                        padding:12px;
                        background:#f7faf8;
                        border:1px solid #e4ebe7;
                        border-radius:11px;
                    ">

                <small style="
                            display:block;
                            color:#7a8781;
                            margin-bottom:5px;
                        ">
                    Verified bank
                </small>


                <strong style="
                            font-size:11px;
                            color:#087443;
                        ">

                    <i class="fa-solid fa-circle-check"></i>

                    {{ $activeAccount->bank_name }}

                    ••••{{ $activeAccount->account_number_last4 }}

                </strong>

            </div>



            {{-- Provider --}}

            <div style="
                        padding:12px;
                        background:#f7faf8;
                        border:1px solid #e4ebe7;
                        border-radius:11px;
                    ">

                <small style="
                            display:block;
                            color:#7a8781;
                            margin-bottom:5px;
                        ">
                    Provider
                </small>


                <strong style="
                            font-size:11px;
                            color:#087443;
                        ">

                    <i class="fa-solid fa-circle-check"></i>

                    {{
            $kycWasReused
            ? 'Paystack verification reused'
            : 'Paystack verified'
                        }}

                </strong>

            </div>

        </div>



        @if($kyc->auto_verified_at)

            <div style="
                            margin-top:14px;
                            color:#7b8781;
                            font-size:10px;
                        ">

                Verified automatically on

                {{
                    $kyc
                        ->auto_verified_at
                        ->format(
                            'd M Y, h:i A'
                        )
                        }}

            </div>

        @endif



        {{-- ============================================================
        NO ACTIVE BANK
        ============================================================= --}}

    @elseif(!$activeAccount)

        <div class="wallet-alert wallet-alert-info">

            <i class="fa-solid fa-building-columns"></i>


            <div>

                <strong>
                    Add your withdrawal bank first.
                </strong>


                <br>


                Paystack identity verification requires a verified
                active bank account connected to your BVN.


                <br><br>


                <a href="#bank-accounts" style="
                            color:#0b6947;
                            font-weight:800;
                        ">

                    Add bank account →

                </a>

            </div>

        </div>



        {{-- ============================================================
        PROCESSING
        ============================================================= --}}

    @elseif($kycProcessing)

        <div class="wallet-alert wallet-alert-info" id="paystackKycProcessingBox">

            <i class="fa-solid fa-spinner fa-spin"></i>


            <div>

                <strong>
                    Paystack is verifying your identity
                </strong>


                <br>


                Paystack is checking the BVN against


                <strong>
                    {{ $activeAccount->account_name }}
                </strong>


                at


                <strong>
                    {{ $activeAccount->bank_name }}
                </strong>


                ••••{{ $activeAccount->account_number_last4 }}.


                <br><br>


                This page will automatically refresh when Paystack
                returns the final verification result.


                <div id="paystackKycSlowNotice" hidden style="margin-top:12px;">
                    This is taking longer than usual. Do not submit again.
                    Midpoint is still waiting for Paystack's signed result.
                    If no result arrives within
                    {{ config('midpoint.kyc.processing_timeout_minutes', 30) }}
                    minutes, this verification will become available for a
                    safe retry.
                </div>

            </div>

        </div>



        {{-- ============================================================
        FORM
        ============================================================= --}}

    @else


        {{-- Previous KYC belongs to different bank --}}

        @if(
                $kyc
                &&
                $kyc->status
                ===
                \App\Models\SellerKycVerification::STATUS_APPROVED
                &&
                !$kycMatchesActiveBank
            )

            <div class="wallet-alert wallet-alert-info">

                <i class="fa-solid fa-building-columns"></i>


                <div>

                    <strong>
                        Verify your newly active bank account.
                    </strong>


                    <br>


                    Your previous identity verification was tied to
                    another withdrawal account.

                    For security, Paystack must validate your BVN
                    against this newly active bank before withdrawals
                    are unlocked again.

                </div>

            </div>

        @endif



        {{-- Failed --}}

        @if(
                $kyc
                &&
                $kyc->status
                ===
                \App\Models\SellerKycVerification::STATUS_REJECTED
            )

            <div class="wallet-kyc-rejection">

                <strong>
                    Verification was not successful
                </strong>


                <br><br>


                {{
                    $kyc->failure_message
                    ?:
                    'Paystack could not verify the submitted BVN and bank account.'
                        }}


                <br><br>


                Check your name, BVN and active bank account,
                then try again.

            </div>

        @endif



        {{-- Provider Error --}}

        @if(
                $kyc
                &&
                $kyc->status
                ===
                \App\Models\SellerKycVerification::STATUS_PROVIDER_ERROR
            )

            <div class="wallet-alert wallet-alert-info">

                <i class="fa-solid fa-rotate"></i>


                <div>

                    <strong>
                        Verification could not be started.
                    </strong>


                    <br>


                    {{ $kyc->failure_message }}


                    <br><br>


                    You can retry below.

                    No admin approval is required.

                </div>

            </div>

        @endif



        {{-- Current Bank --}}

        <div class="wallet-alert wallet-alert-info">

            <i class="fa-solid fa-building-columns"></i>


            <div>

                Paystack will validate your BVN against:


                <strong>
                    {{ $activeAccount->account_name }}
                </strong>


                at


                <strong>
                    {{ $activeAccount->bank_name }}
                </strong>


                ••••{{ $activeAccount->account_number_last4 }}

            </div>

        </div>



        {{-- ========================================================
        PAYSTACK KYC FORM
        ========================================================= --}}

        <form method="POST" action="{{ route('seller.wallet.kyc.store') }}" id="paystackKycForm">

            @csrf


            <div class="wallet-form-grid">


                {{-- First Name --}}

                <div class="wallet-field">

                    <label for="kycFirstName">
                        First name
                    </label>


                    <input id="kycFirstName" type="text" name="first_name" value="{{
            old(
                'first_name',
                $defaultFirstName
            )
                            }}" maxlength="100" autocomplete="given-name" placeholder="Exactly as registered on your BVN"
                        required>

                </div>



                {{-- Middle Name --}}

                <div class="wallet-field">

                    <label for="kycMiddleName">

                        Middle name

                        <small style="
                                    font-weight:400;
                                    color:#89958f;
                                ">
                            Optional
                        </small>

                    </label>


                    <input id="kycMiddleName" type="text" name="middle_name" value="{{
            old(
                'middle_name',
                $defaultMiddleName
            )
                            }}" maxlength="100" autocomplete="additional-name" placeholder="If present on your BVN">

                </div>



                {{-- Last Name --}}

                <div class="wallet-field">

                    <label for="kycLastName">
                        Last name
                    </label>


                    <input id="kycLastName" type="text" name="last_name" value="{{
            old(
                'last_name',
                $defaultLastName
            )
                            }}" maxlength="100" autocomplete="family-name" placeholder="Exactly as registered on your BVN"
                        required>

                </div>



                {{-- DOB --}}

                <div class="wallet-field">

                    <label for="kycDateOfBirth">
                        Date of birth
                    </label>


                    <input id="kycDateOfBirth" type="date" name="date_of_birth" value="{{
            old(
                'date_of_birth',
                $kyc
                &&
                $kyc->date_of_birth
                ? $kyc
                    ->date_of_birth
                    ->format(
                        'Y-m-d'
                    )
                : ''
            )
                            }}" max="{{ now()->subDay()->format('Y-m-d') }}" required>


                    <small style="
                                display:block;
                                margin-top:5px;
                                color:#7c8882;
                                font-size:9px;
                                line-height:1.5;
                            ">

                        Stored as part of your Midpoint KYC record.

                        Paystack's BVN-bank verification uses your
                        BVN, legal name and bank details.

                    </small>

                </div>



                {{-- BVN --}}

                <div class="wallet-field wallet-field-full">

                    <label for="kycBvn">
                        Bank Verification Number (BVN)
                    </label>


                    <input id="kycBvn" type="password" name="bvn" maxlength="11" minlength="11" inputmode="numeric"
                        pattern="[0-9]{11}" autocomplete="off" placeholder="Enter your 11-digit BVN" required>


                    <small style="
                                display:block;
                                margin-top:6px;
                                color:#7c8882;
                                font-size:9px;
                                line-height:1.55;
                            ">

                        Your BVN is sent securely from Midpoint's server
                        to Paystack.

                        Midpoint stores the full BVN encrypted and only
                        displays the last four digits.

                    </small>

                </div>

            </div>



            {{-- Checks --}}

            <div style="
                        margin:4px 0 16px;
                        padding:13px;
                        border-radius:11px;
                        background:#f6f9f7;
                        color:#53635a;
                        font-size:10px;
                        line-height:1.7;
                    ">

                <strong style="
                            display:block;
                            margin-bottom:5px;
                            color:#17372a;
                        ">

                    Paystack automatic checks

                </strong>


                <div>
                    ✓ Bank account exists and is valid
                </div>


                <div>
                    ✓ BVN is valid
                </div>


                <div>
                    ✓ BVN is connected to the active bank account
                </div>


                <div>
                    ✓ Customer identity information is validated
                </div>


                <div style="
                            margin-top:7px;
                            color:#7c8882;
                        ">

                    This Paystack validation flow does not perform
                    selfie or liveness verification, so Midpoint no
                    longer asks sellers to upload a selfie.

                </div>

            </div>



            <button type="submit" class="wallet-button" id="submitPaystackKyc">

                <i class="fa-solid fa-shield-halved"></i>

                Verify identity with Paystack

            </button>

        </form>

    @endif

</section>



{{-- ================================================================
KYC JAVASCRIPT
================================================================= --}}

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const form =
                document.getElementById(
                    'paystackKycForm'
                );


            const button =
                document.getElementById(
                    'submitPaystackKyc'
                );


            const bvn =
                document.getElementById(
                    'kycBvn'
                );


            /*
            |--------------------------------------------------------------------------
            | BVN Digits Only
            |--------------------------------------------------------------------------
            */

            if (bvn) {

                bvn.addEventListener(
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
                                    11
                                );

                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Submit State
            |--------------------------------------------------------------------------
            */

            if (
                form
                &&
                button
            ) {

                form.addEventListener(
                    'submit',
                    function () {

                        if (
                            bvn
                            &&
                            !/^\d{11}$/.test(
                                bvn.value
                            )
                        ) {

                            return;
                        }


                        button.disabled =
                            true;


                        button.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin"></i> Sending to Paystack...';

                    }
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Automatically Poll Local Status
            |--------------------------------------------------------------------------
            |
            | Paystack sends the actual result to our webhook.
            |
            | This simply detects when our database has been updated.
            |
            */

            @if($kycProcessing)

                const kycPollingStartedAt =
                    Date.now();


                const kycSlowNotice =
                    document.getElementById(
                        'paystackKycSlowNotice'
                    );


                const pollKycStatus =
                    async function () {

                        try {

                            const response =
                                await fetch(
                                    '{{ route('seller.wallet.kyc.status') }}',
                                    {

                                        method:
                                            'GET',


                                        headers: {

                                            'Accept':
                                                'application/json',


                                            'X-Requested-With':
                                                'XMLHttpRequest',

                                        },


                                        credentials:
                                            'same-origin',


                                        cache:
                                            'no-store',

                                    }
                                );


                            if (
                                response.ok
                            ) {

                                const data =
                                    await response.json();


                                if (
                                    data.status
                                    ===
                                    'approved'

                                    ||

                                    data.status
                                    ===
                                    'rejected'

                                    ||

                                    data.status
                                    ===
                                    'provider_error'
                                ) {

                                    window
                                        .location
                                        .reload();


                                    return;
                                }
                            }

                        } catch (
                        error
                        ) {

                            /*
                             * Ignore polling errors.
                             *
                             * Paystack webhook remains the source of truth.
                             */

                        }


                        const elapsed =
                            Date.now()
                            -
                            kycPollingStartedAt;


                        if (
                            elapsed >= 120000
                            && kycSlowNotice
                        ) {
                            kycSlowNotice.hidden = false;
                        }


                        const nextDelay =
                            elapsed < 120000
                                ? 4000
                                : elapsed < 600000
                                    ? 15000
                                    : 30000;


                        window.setTimeout(
                            pollKycStatus,
                            nextDelay
                        );

                    };


                window.setTimeout(
                    pollKycStatus,
                    3000
                );

            @endif

    }
    );

</script>