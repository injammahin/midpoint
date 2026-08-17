{{-- ================================================================
    AUTOMATED KYC
================================================================= --}}

<section
    id="kyc"
    class="wallet-card"
>

    <div class="wallet-card-header">

        <div>

            <h2>
                Identity verification
            </h2>


            <p>
                Verify your Nigerian identity automatically using
                NIN or BVN and a live selfie.
                No manual admin approval is required.
            </p>

        </div>



        @if(
            $kyc
            &&
            $kyc->status
            ===
            \App\Models\SellerKycVerification::STATUS_APPROVED
        )

            <span class="wallet-badge wallet-badge-success">

                <i class="fa-solid fa-circle-check"></i>

                Verified

            </span>


        @elseif(
            $kyc
            &&
            $kyc->status
            ===
            \App\Models\SellerKycVerification::STATUS_PROCESSING
        )

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
        APPROVED
    ============================================================= --}}

    @if(
        $kyc
        &&
        $kyc->status
        ===
        \App\Models\SellerKycVerification::STATUS_APPROVED
    )

        <div class="wallet-alert wallet-alert-success">

            <i class="fa-solid fa-shield-halved"></i>


            <div>

                <strong>
                    Identity verified automatically
                </strong>

                <br>

                Your government identity, selfie and
                withdrawal bank account passed verification.

            </div>

        </div>



        <div
            style="
                display:grid;
                grid-template-columns:
                    repeat(
                        auto-fit,
                        minmax(160px, 1fr)
                    );
                gap:10px;
                margin-top:15px;
            "
        >


            {{-- Government identity --}}

            <div
                style="
                    padding:12px;
                    background:#f7faf8;
                    border:1px solid #e4ebe7;
                    border-radius:11px;
                "
            >

                <small
                    style="
                        display:block;
                        color:#7a8781;
                        margin-bottom:5px;
                    "
                >
                    Government identity
                </small>


                <strong
                    style="
                        font-size:11px;
                        color:#183529;
                    "
                >
                    {{ $kyc->verified_full_name }}
                </strong>

            </div>



            {{-- ID --}}

            <div
                style="
                    padding:12px;
                    background:#f7faf8;
                    border:1px solid #e4ebe7;
                    border-radius:11px;
                "
            >

                <small
                    style="
                        display:block;
                        color:#7a8781;
                        margin-bottom:5px;
                    "
                >
                    Identity
                </small>


                <strong
                    style="
                        font-size:11px;
                        color:#183529;
                    "
                >

                    {{ strtoupper($kyc->id_type) }}

                    ••••{{ $kyc->id_number_last4 }}

                </strong>

            </div>



            {{-- Face --}}

            <div
                style="
                    padding:12px;
                    background:#f7faf8;
                    border:1px solid #e4ebe7;
                    border-radius:11px;
                "
            >

                <small
                    style="
                        display:block;
                        color:#7a8781;
                        margin-bottom:5px;
                    "
                >
                    Face match
                </small>


                <strong
                    style="
                        font-size:11px;
                        color:#087443;
                    "
                >

                    <i class="fa-solid fa-circle-check"></i>

                    {{ number_format(
                        (float) $kyc->face_confidence,
                        1
                    ) }}%

                </strong>

            </div>



            {{-- Liveness --}}

            <div
                style="
                    padding:12px;
                    background:#f7faf8;
                    border:1px solid #e4ebe7;
                    border-radius:11px;
                "
            >

                <small
                    style="
                        display:block;
                        color:#7a8781;
                        margin-bottom:5px;
                    "
                >
                    Liveness
                </small>


                <strong
                    style="
                        font-size:11px;
                        color:#087443;
                    "
                >

                    <i class="fa-solid fa-circle-check"></i>

                    {{ number_format(
                        (float) $kyc->liveness_probability,
                        1
                    ) }}%

                </strong>

            </div>



            {{-- Bank --}}

            <div
                style="
                    padding:12px;
                    background:#f7faf8;
                    border:1px solid #e4ebe7;
                    border-radius:11px;
                "
            >

                <small
                    style="
                        display:block;
                        color:#7a8781;
                        margin-bottom:5px;
                    "
                >
                    Active bank ownership
                </small>


                @if($bankIdentityMatches)

                    <strong
                        style="
                            font-size:11px;
                            color:#087443;
                        "
                    >

                        <i class="fa-solid fa-circle-check"></i>

                        Matched

                    </strong>

                @else

                    <strong
                        style="
                            font-size:11px;
                            color:#c43838;
                        "
                    >

                        <i class="fa-solid fa-circle-xmark"></i>

                        Current bank mismatch

                    </strong>

                @endif

            </div>

        </div>



        @if(
            $kyc->auto_verified_at
        )

            <div
                style="
                    margin-top:14px;
                    color:#7b8781;
                    font-size:10px;
                "
            >

                Verified automatically on

                {{ $kyc
                    ->auto_verified_at
                    ->format(
                        'd M Y, h:i A'
                    )
                }}

            </div>

        @endif



    {{-- ============================================================
        NEED BANK FIRST
    ============================================================= --}}

    @elseif(!$activeAccount)

        <div class="wallet-alert wallet-alert-info">

            <i class="fa-solid fa-building-columns"></i>


            <div>

                <strong>
                    Add your withdrawal bank first.
                </strong>

                <br>

                Your verified identity must also match
                the owner of your active withdrawal bank account.

                <br><br>

                <a
                    href="#bank-accounts"
                    style="
                        color:#0b6947;
                        font-weight:800;
                    "
                >
                    Add bank account →
                </a>

            </div>

        </div>



    {{-- ============================================================
        FORM
    ============================================================= --}}

    @else


        {{-- Rejected --}}

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

                {{ $kyc->failure_message }}

                <br><br>

                Check your information and submit again.

            </div>

        @endif



        {{-- Provider error --}}

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

                    {{ $kyc->failure_message }}

                    <br>

                    No admin approval is required.
                    Simply retry the verification.

                </div>

            </div>

        @endif



        {{-- Active bank information --}}

        <div class="wallet-alert wallet-alert-info">

            <i class="fa-solid fa-building-columns"></i>


            <div>

                Your identity will also be compared with:

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



        <form
            method="POST"
            action="{{ route('seller.wallet.kyc.store') }}"
            enctype="multipart/form-data"
            id="automatedKycForm"
        >

            @csrf



            <div class="wallet-form-grid">


                {{-- Legal name --}}

                <div class="wallet-field">

                    <label for="kycLegalName">
                        Full legal name
                    </label>


                    <input
                        id="kycLegalName"
                        type="text"
                        name="legal_name"
                        value="{{
                            old(
                                'legal_name',
                                $kyc
                                    ? $kyc->legal_name
                                    : $seller->name
                            )
                        }}"
                        placeholder="Exactly as on your NIN/BVN"
                        required
                    >


                    <small
                        style="
                            display:block;
                            margin-top:5px;
                            color:#7c8882;
                            font-size:9px;
                        "
                    >
                        Enter your government-registered name.
                    </small>

                </div>



                {{-- DOB --}}

                <div class="wallet-field">

                    <label for="kycDateOfBirth">
                        Date of birth
                    </label>


                    <input
                        id="kycDateOfBirth"
                        type="date"
                        name="date_of_birth"
                        value="{{
                            old(
                                'date_of_birth',
                                $kyc
                                &&
                                $kyc->date_of_birth
                                    ? $kyc
                                        ->date_of_birth
                                        ->format('Y-m-d')
                                    : ''
                            )
                        }}"
                        max="{{ now()->subDay()->format('Y-m-d') }}"
                        required
                    >

                </div>



                {{-- ID Type --}}

                <div class="wallet-field">

                    <label for="kycIdType">
                        Identity type
                    </label>


                    <select
                        id="kycIdType"
                        name="id_type"
                        required
                    >

                        <option value="">
                            Select identity type
                        </option>


                        <option
                            value="nin"
                            {{
                                old(
                                    'id_type',
                                    $kyc
                                        ? $kyc->id_type
                                        : ''
                                )
                                ===
                                'nin'
                                    ? 'selected'
                                    : ''
                            }}
                        >
                            National Identification Number (NIN)
                        </option>


                        <option
                            value="bvn"
                            {{
                                old(
                                    'id_type',
                                    $kyc
                                        ? $kyc->id_type
                                        : ''
                                )
                                ===
                                'bvn'
                                    ? 'selected'
                                    : ''
                            }}
                        >
                            Bank Verification Number (BVN)
                        </option>

                    </select>

                </div>



                {{-- ID number --}}

                <div class="wallet-field">

                    <label for="kycIdNumber">
                        NIN / BVN
                    </label>


                    <input
                        id="kycIdNumber"
                        type="text"
                        name="id_number"
                        maxlength="11"
                        minlength="11"
                        inputmode="numeric"
                        pattern="[0-9]{11}"
                        autocomplete="off"
                        placeholder="11-digit number"
                        required
                    >


                    <small
                        style="
                            display:block;
                            margin-top:5px;
                            color:#7c8882;
                            font-size:9px;
                        "
                    >
                        Your full number is stored encrypted.
                    </small>

                </div>



                {{-- Selfie --}}

                <div class="wallet-field wallet-field-full">

                    <label for="kycSelfie">
                        Live selfie
                    </label>


                    <input
                        id="kycSelfie"
                        type="file"
                        name="selfie"
                        accept="image/jpeg,image/png"
                        required
                    >


                    <small
                        style="
                            display:block;
                            margin-top:6px;
                            color:#7c8882;
                            font-size:9px;
                            line-height:1.55;
                        "
                    >
                        Use a recent, clear selfie.
                        Face the camera directly, use good lighting,
                        remove sunglasses and avoid filters.
                        The uploaded selfie is sent for verification
                        and is not permanently stored by Midpoint.
                    </small>

                </div>

            </div>



            {{-- What happens --}}

            <div
                style="
                    margin:4px 0 16px;
                    padding:13px;
                    border-radius:11px;
                    background:#f6f9f7;
                    color:#53635a;
                    font-size:10px;
                    line-height:1.7;
                "
            >

                <strong
                    style="
                        display:block;
                        margin-bottom:5px;
                        color:#17372a;
                    "
                >
                    Automatic checks
                </strong>

                <div>
                    ✓ Government identity
                </div>

                <div>
                    ✓ Selfie face match
                </div>

                <div>
                    ✓ Liveness detection
                </div>

                <div>
                    ✓ Legal name
                </div>

                <div>
                    ✓ Date of birth
                </div>

                <div>
                    ✓ Withdrawal bank ownership
                </div>

            </div>



            <button
                type="submit"
                class="wallet-button"
                id="submitAutomaticKyc"
            >

                <i class="fa-solid fa-shield-halved"></i>

                Verify my identity

            </button>

        </form>

    @endif

</section>



{{-- ================================================================
    AUTOMATED KYC JS
================================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'automatedKycForm'
            );


        const button =
            document.getElementById(
                'submitAutomaticKyc'
            );


        const idNumber =
            document.getElementById(
                'kycIdNumber'
            );


        if (
            idNumber
        ) {

            idNumber.addEventListener(
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


        if (
            form
            &&
            button
        ) {

            form.addEventListener(
                'submit',
                function () {

                    button.disabled =
                        true;


                    button.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> Verifying identity...';
                }
            );
        }

    }
);

</script>