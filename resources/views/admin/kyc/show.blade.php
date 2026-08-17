@extends('admin.layouts.app')

@section('title', 'Review Seller KYC')

@push('styles')
<style>
    .kyc-show {
        max-width: 900px;
    }

    .kyc-back {
        display: inline-block;
        margin-bottom: 14px;
        color: #0b6947;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
    }

    .kyc-panel {
        background: #fff;
        border: 1px solid #e2e8e5;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .kyc-panel h1,
    .kyc-panel h2 {
        margin: 0 0 14px;
        color: #17251f;
    }

    .kyc-panel h1 {
        font-size: 24px;
    }

    .kyc-panel h2 {
        font-size: 16px;
    }

    .kyc-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .kyc-item {
        padding: 12px;
        border-radius: 11px;
        background: #f7f9f8;
    }

    .kyc-item span {
        display: block;
        color: #7b8781;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: 5px;
    }

    .kyc-item strong {
        font-size: 12px;
        color: #25352d;
    }

    .kyc-docs {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .kyc-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 0;
        border-radius: 10px;
        padding: 10px 13px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .kyc-btn.primary {
        background: #0b3d2e;
        color: #fff;
    }

    .kyc-btn.danger {
        background: #fff0f0;
        color: #b82e2e;
    }

    .kyc-btn.secondary {
        background: #eff4f1;
        color: #27493b;
    }

    .kyc-alert {
        padding: 11px 13px;
        border-radius: 10px;
        margin-bottom: 14px;
        font-size: 11px;
    }

    .kyc-alert.success {
        background: #e9f9f0;
        color: #087443;
    }

    .kyc-alert.error {
        background: #fff0f0;
        color: #b22f2f;
    }

    .reject-form {
        margin-top: 14px;
    }

    .reject-form textarea {
        width: 100%;
        min-height: 90px;
        border: 1px solid #dce4e0;
        border-radius: 10px;
        padding: 11px;
        font: 12px Inter, sans-serif;
        margin: 8px 0;
    }

    @media(max-width: 700px) {
        .kyc-meta {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush


@section('content')

<div class="kyc-show">

    <a
        href="{{ route('admin.seller-kyc.index') }}"
        class="kyc-back"
    >
        ← Back to KYC
    </a>


    @if(session('success'))

        <div class="kyc-alert success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="kyc-alert error">
            {{ session('error') }}
        </div>

    @endif


    @if($errors->any())

        <div class="kyc-alert error">
            {{ $errors->first() }}
        </div>

    @endif


    <section class="kyc-panel">

        <h1>
            Review KYC
        </h1>


        <div class="kyc-meta">

            <div class="kyc-item">
                <span>Seller</span>
                <strong>
                    {{ $kyc->seller?->name }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>Email</span>
                <strong>
                    {{ $kyc->seller?->email }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>Legal name</span>
                <strong>
                    {{ $kyc->legal_name }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>Date of birth</span>
                <strong>
                    {{
                        $kyc
                            ->date_of_birth
                            ?->format(
                                'd M Y'
                            )
                    }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>ID type</span>
                <strong>
                    {{
                        strtoupper(
                            str_replace(
                                '_',
                                ' ',
                                $kyc->id_type
                            )
                        )
                    }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>ID number</span>
                <strong>
                    {{ $kyc->id_number }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>Status</span>
                <strong>
                    {{ $kyc->status_label }}
                </strong>
            </div>


            <div class="kyc-item">
                <span>Submitted</span>
                <strong>
                    {{
                        optional(
                            $kyc->submitted_at
                        )->format(
                            'd M Y, h:i A'
                        )
                    }}
                </strong>
            </div>

        </div>

    </section>


    <section class="kyc-panel">

        <h2>
            Verification documents
        </h2>


        <div class="kyc-docs">

            <a
                class="kyc-btn secondary"
                href="{{
                    route(
                        'admin.seller-kyc.document',
                        [
                            $kyc,
                            'front',
                        ]
                    )
                }}"
            >

                <i class="fa-solid fa-id-card"></i>

                ID front

            </a>


            @if($kyc->document_back_path)

                <a
                    class="kyc-btn secondary"
                    href="{{
                        route(
                            'admin.seller-kyc.document',
                            [
                                $kyc,
                                'back',
                            ]
                        )
                    }}"
                >

                    <i class="fa-solid fa-id-card"></i>

                    ID back

                </a>

            @endif


            <a
                class="kyc-btn secondary"
                href="{{
                    route(
                        'admin.seller-kyc.document',
                        [
                            $kyc,
                            'selfie',
                        ]
                    )
                }}"
            >

                <i class="fa-solid fa-camera"></i>

                Selfie

            </a>

        </div>

    </section>


    @if(
        $kyc->status
        ===
        \App\Models\SellerKycVerification::STATUS_PENDING
    )

        <section class="kyc-panel">

            <h2>
                Decision
            </h2>


            <form
                method="POST"
                action="{{
                    route(
                        'admin.seller-kyc.approve',
                        $kyc
                    )
                }}"
                style="
                    display:inline-block;
                    margin-right:8px;
                "
            >

                @csrf
                @method('PATCH')


                <button
                    type="submit"
                    class="kyc-btn primary"
                    onclick="
                        return confirm(
                            'Approve this seller KYC?'
                        )
                    "
                >

                    <i class="fa-solid fa-circle-check"></i>

                    Approve KYC

                </button>

            </form>


            <form
                method="POST"
                action="{{
                    route(
                        'admin.seller-kyc.reject',
                        $kyc
                    )
                }}"
                class="reject-form"
            >

                @csrf
                @method('PATCH')


                <label
                    style="
                        font-size:11px;
                        font-weight:800;
                    "
                >
                    Reason for rejection
                </label>


                <textarea
                    name="rejection_reason"
                    required
                    placeholder="Tell the seller exactly what must be corrected..."
                >{{ old('rejection_reason') }}</textarea>


                <button
                    type="submit"
                    class="kyc-btn danger"
                    onclick="
                        return confirm(
                            'Reject this KYC submission?'
                        )
                    "
                >

                    <i class="fa-solid fa-circle-xmark"></i>

                    Reject KYC

                </button>

            </form>

        </section>


    @elseif(
        $kyc->status
        ===
        \App\Models\SellerKycVerification::STATUS_REJECTED
    )

        <section class="kyc-panel">

            <h2>
                Rejection reason
            </h2>

            <p
                style="
                    font-size:12px;
                    color:#6a7770;
                "
            >
                {{ $kyc->rejection_reason }}
            </p>

        </section>

    @endif

</div>

@endsection