@extends('seller.layouts.app')


@section('title', 'Business Profile')


@section('content')

@php
    $initials = collect(
        preg_split(
            '/\s+/',
            trim($businessName)
        )
    )
        ->filter()
        ->take(2)
        ->map(
            fn ($word) =>
                strtoupper(
                    substr(
                        $word,
                        0,
                        1
                    )
                )
        )
        ->implode('');
@endphp


<div class="bp-page">


    {{-- =========================================================
        FLASH MESSAGE
    ========================================================== --}}

    @if (session('success'))

        <div class="bp-alert bp-alert-success">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif


    @if ($errors->any())

        <div class="bp-alert bp-alert-error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <strong>
                    Please check the information below.
                </strong>

                <span>
                    {{ $errors->first() }}
                </span>

            </div>

        </div>

    @endif



    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}

    <div class="bp-page-header">

        <div>

            <div class="bp-eyebrow">
                Seller profile
            </div>


            <h1>
                Business profile
            </h1>


            <p>
                Manage how your verified business appears to buyers on MidPoint.
            </p>

        </div>


        <a
            href="{{ route('featured-businesses') }}"
            class="bp-preview-button"
            target="_blank"
        >

            <i class="fa-regular fa-eye"></i>

            View featured businesses

        </a>

    </div>



    <form
        method="POST"
        action="{{ route('seller.business-profile.update') }}"
        enctype="multipart/form-data"
    >

        @csrf
        @method('PUT')


        <div class="bp-grid">


            {{-- =====================================================
                LEFT
            ====================================================== --}}

            <div class="bp-column">


                {{-- =================================================
                    BRAND PROFILE
                ================================================== --}}

                <section class="bp-card">

                    <div class="bp-card-heading">

                        <div class="bp-heading-icon">
                            <i class="fa-solid fa-store"></i>
                        </div>


                        <div>

                            <h2>
                                Shop identity
                            </h2>

                            <p>
                                Your business branding and verified information.
                            </p>

                        </div>

                    </div>



                    {{-- =============================================
                        PROFILE IMAGE
                    ============================================== --}}

                    <div class="bp-profile-image-section">

                        <div
                            class="bp-profile-image"
                            id="businessProfilePreview"
                        >

                            @if ($profile->profile_image_url)

                                <img
                                    src="{{ $profile->profile_image_url }}"
                                    alt="{{ $businessName }}"
                                    id="businessProfilePreviewImage"
                                >

                                <span
                                    id="businessProfilePreviewInitials"
                                    hidden
                                >
                                    {{ $initials }}
                                </span>

                            @else

                                <img
                                    src=""
                                    alt=""
                                    id="businessProfilePreviewImage"
                                    hidden
                                >

                                <span id="businessProfilePreviewInitials">
                                    {{ $initials }}
                                </span>

                            @endif

                        </div>


                        <div class="bp-profile-image-copy">

                            <strong>
                                Business profile picture
                            </strong>


                            <p>
                                Upload your logo or shop profile picture.
                                JPG, PNG or WEBP up to 3 MB.
                            </p>


                            <div class="bp-image-actions">

                                <label class="bp-upload-button">

                                    <i class="fa-solid fa-camera"></i>

                                    Choose image


                                    <input
                                        type="file"
                                        name="profile_image"
                                        id="businessProfileImageInput"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        hidden
                                    >

                                </label>


                                @if ($profile->profile_image_path)

                                    <button
                                        type="button"
                                        class="bp-remove-image"
                                        data-open-delete-image
                                    >

                                        <i class="fa-regular fa-trash-can"></i>

                                        Remove

                                    </button>

                                @endif

                            </div>

                        </div>

                    </div>



                    <div class="bp-divider"></div>



                    {{-- =============================================
                        VERIFIED INFORMATION
                    ============================================== --}}

                    <div class="bp-verified-box">

                        <div class="bp-verified-icon">

                            <i class="fa-solid fa-shield-halved"></i>

                        </div>


                        <div>

                            <strong>
                                MidPoint verified identity
                            </strong>


                            <p>
                                Your verified business name and category come from
                                your approved seller application.
                            </p>

                        </div>


                        <span>
                            Verified
                        </span>

                    </div>



                    <div class="bp-two-fields">

                        <div class="bp-field">

                            <label>
                                Verified business name
                            </label>


                            <div class="bp-readonly">

                                <i class="fa-solid fa-store"></i>

                                <span>
                                    {{ $businessName }}
                                </span>

                                <small>
                                    Locked
                                </small>

                            </div>

                        </div>


                        <div class="bp-field">

                            <label>
                                Verified category
                            </label>


                            <div class="bp-readonly">

                                <i class="fa-solid fa-layer-group"></i>

                                <span>
                                    {{ $category ?: 'Not specified' }}
                                </span>

                                <small>
                                    Locked
                                </small>

                            </div>

                        </div>

                    </div>



                    {{-- =============================================
                        TAGLINE
                    ============================================== --}}

                    <div class="bp-field">

                        <label for="businessTagline">

                            Shop tagline

                            <small>
                                Max 150 characters
                            </small>

                        </label>


                        <input
                            id="businessTagline"
                            type="text"
                            name="tagline"
                            maxlength="150"
                            value="{{ old('tagline', $profile->tagline) }}"
                            placeholder="e.g. Trusted phones, laptops and accessories"
                        >

                    </div>



                    {{-- =============================================
                        ABOUT
                    ============================================== --}}

                    <div class="bp-field">

                        <label for="businessAbout">

                            About your shop

                            <small>
                                Max 3000 characters
                            </small>

                        </label>


                        <textarea
                            id="businessAbout"
                            name="about"
                            maxlength="3000"
                            rows="9"
                            placeholder="Tell buyers about your shop, the products you sell, your experience, warranty policy and why they can trust your business..."
                        >{{ old('about', $profile->about) }}</textarea>


                        <div class="bp-textarea-footer">

                            <span>
                                This description appears on your public business profile.
                            </span>


                            <strong id="businessAboutCount">
                                0 / 3000
                            </strong>

                        </div>

                    </div>

                </section>



                {{-- =================================================
                    ONLINE PRESENCE
                ================================================== --}}

                <section class="bp-card">

                    <div class="bp-card-heading">

                        <div class="bp-heading-icon purple">
                            <i class="fa-solid fa-globe"></i>
                        </div>


                        <div>

                            <h2>
                                Online presence
                            </h2>

                            <p>
                                Give buyers more ways to learn about your business.
                            </p>

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Website
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-solid fa-globe"></i>

                            <input
                                type="url"
                                name="website_url"
                                value="{{ old('website_url', $profile->website_url) }}"
                                placeholder="https://yourbusiness.com"
                            >

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Instagram
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-brands fa-instagram"></i>

                            <input
                                type="url"
                                name="instagram_url"
                                value="{{ old('instagram_url', $profile->instagram_url) }}"
                                placeholder="https://instagram.com/yourbusiness"
                            >

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Facebook
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-brands fa-facebook-f"></i>

                            <input
                                type="url"
                                name="facebook_url"
                                value="{{ old('facebook_url', $profile->facebook_url) }}"
                                placeholder="https://facebook.com/yourbusiness"
                            >

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Business hours
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-regular fa-clock"></i>

                            <input
                                type="text"
                                name="business_hours"
                                value="{{ old('business_hours', $profile->business_hours) }}"
                                placeholder="e.g. Mon - Sat, 9:00 AM - 6:00 PM"
                            >

                        </div>

                    </div>

                </section>

            </div>



            {{-- =====================================================
                RIGHT
            ====================================================== --}}

            <div class="bp-column">


                {{-- =================================================
                    CONTACT
                ================================================== --}}

                <section class="bp-card">

                    <div class="bp-card-heading">

                        <div class="bp-heading-icon">
                            <i class="fa-solid fa-address-card"></i>
                        </div>


                        <div>

                            <h2>
                                Contact information
                            </h2>

                            <p>
                                Information buyers can use to reach your business.
                            </p>

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Business location
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-solid fa-location-dot"></i>

                            <input
                                type="text"
                                name="location"
                                value="{{ old('location', $profile->location) }}"
                                placeholder="e.g. Ikeja, Lagos"
                            >

                        </div>

                    </div>



                    <div class="bp-field">

                        <label>
                            Business phone
                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-solid fa-phone"></i>

                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone', $profile->phone) }}"
                                placeholder="e.g. +234 803 552 1194"
                            >

                        </div>

                    </div>



                    <div class="bp-public-email">

                        <div>

                            <span>
                                Account email
                            </span>

                            <strong>
                                {{ $user->email }}
                            </strong>

                        </div>


                        @if ($user->hasVerifiedEmail())

                            <span class="bp-email-verified">

                                <i class="fa-solid fa-circle-check"></i>

                                Verified

                            </span>

                        @endif

                    </div>



                    <div class="bp-divider"></div>



                    <h3 class="bp-mini-title">
                        Public visibility
                    </h3>



                    <label class="bp-toggle-row">

                        <div>

                            <strong>
                                Show business phone
                            </strong>

                            <span>
                                Buyers can see your business phone number.
                            </span>

                        </div>


                        <span class="bp-switch">

                            <input
                                type="checkbox"
                                name="show_phone"
                                value="1"
                                @checked(old('show_phone', $profile->show_phone))
                            >

                            <span></span>

                        </span>

                    </label>



                    <label class="bp-toggle-row">

                        <div>

                            <strong>
                                Show email
                            </strong>

                            <span>
                                Display your verified MidPoint email publicly.
                            </span>

                        </div>


                        <span class="bp-switch">

                            <input
                                type="checkbox"
                                name="show_email"
                                value="1"
                                @checked(old('show_email', $profile->show_email))
                            >

                            <span></span>

                        </span>

                    </label>

                </section>



                {{-- =================================================
                    WHATSAPP
                ================================================== --}}

                <section class="bp-card bp-whatsapp-card">

                    <div class="bp-card-heading">

                        <div class="bp-heading-icon whatsapp">

                            <i class="fa-brands fa-whatsapp"></i>

                        </div>


                        <div>

                            <h2>
                                WhatsApp
                            </h2>

                            <p>
                                Let buyers start a WhatsApp conversation directly.
                            </p>

                        </div>


                        @if ($profile->whatsapp_enabled && $profile->whatsapp_number)

                            <span class="bp-connected-badge">

                                <i class="fa-solid fa-circle"></i>

                                Connected

                            </span>

                        @endif

                    </div>



                    <div class="bp-whatsapp-info">

                        <i class="fa-brands fa-whatsapp"></i>


                        <div>

                            <strong>
                                Direct buyer messaging
                            </strong>


                            <p>
                                When enabled, a Message on WhatsApp button appears
                                on your public MidPoint business profile.
                            </p>

                        </div>

                    </div>



                    <label class="bp-toggle-row bp-whatsapp-toggle">

                        <div>

                            <strong>
                                Enable WhatsApp contact
                            </strong>

                            <span>
                                Buyers can open WhatsApp directly from MidPoint.
                            </span>

                        </div>


                        <span class="bp-switch green">

                            <input
                                type="checkbox"
                                name="whatsapp_enabled"
                                value="1"
                                id="whatsappEnabled"
                                @checked(old('whatsapp_enabled', $profile->whatsapp_enabled))
                            >

                            <span></span>

                        </span>

                    </label>



                    <div class="bp-field">

                        <label for="whatsappNumber">

                            WhatsApp number

                            <small>
                                Include country code
                            </small>

                        </label>


                        <div class="bp-input-icon">

                            <i class="fa-brands fa-whatsapp"></i>

                            <input
                                id="whatsappNumber"
                                type="text"
                                name="whatsapp_number"
                                value="{{ old('whatsapp_number', $profile->whatsapp_number) }}"
                                placeholder="2348035521194"
                            >

                        </div>


                        <div class="bp-help">

                            Do not use the leading local zero when using a
                            country code. Example: <strong>2348035521194</strong>.

                        </div>

                    </div>



                    <div class="bp-field">

                        <label for="whatsappMessage">

                            Default buyer message

                            <small>
                                Optional
                            </small>

                        </label>


                        <textarea
                            id="whatsappMessage"
                            name="whatsapp_message"
                            maxlength="500"
                            rows="5"
                            placeholder="Hi, I found your verified business on MidPoint and would like to make an enquiry."
                        >{{ old('whatsapp_message', $profile->whatsapp_message) }}</textarea>

                    </div>



                    @if ($profile->whatsappUrl())

                        <a
                            href="{{ $profile->whatsappUrl() }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="bp-test-whatsapp"
                        >

                            <i class="fa-brands fa-whatsapp"></i>

                            Test WhatsApp connection

                            <i class="fa-solid fa-arrow-up-right-from-square"></i>

                        </a>

                    @endif

                </section>



                {{-- =================================================
                    PUBLIC PREVIEW
                ================================================== --}}

                <section class="bp-card">

                    <div class="bp-card-heading">

                        <div class="bp-heading-icon preview">
                            <i class="fa-regular fa-eye"></i>
                        </div>


                        <div>

                            <h2>
                                Public profile preview
                            </h2>

                            <p>
                                A quick preview of what buyers identify first.
                            </p>

                        </div>

                    </div>



                    <div class="bp-public-preview">

                        <div class="bp-preview-avatar">

                            @if ($profile->profile_image_url)

                                <img
                                    src="{{ $profile->profile_image_url }}"
                                    alt="{{ $businessName }}"
                                >

                            @else

                                {{ $initials }}

                            @endif

                        </div>


                        <div class="bp-preview-info">

                            <div>

                                <strong>
                                    {{ $businessName }}
                                </strong>


                                <span class="bp-preview-verified">

                                    <i class="fa-solid fa-check"></i>

                                    Verified

                                </span>

                            </div>


                            <p>
                                {{ $category ?: 'Verified Seller' }}
                            </p>


                            @if ($profile->tagline)

                                <span>
                                    {{ $profile->tagline }}
                                </span>

                            @endif

                        </div>

                    </div>



                    <div class="bp-preview-meta">

                        @if ($profile->location)

                            <span>

                                <i class="fa-solid fa-location-dot"></i>

                                {{ $profile->location }}

                            </span>

                        @endif


                        <span>

                            <i class="fa-solid fa-box"></i>

                            {{ $user->sellerProducts()->where('is_active', true)->count() }}
                            products

                        </span>

                    </div>



                    @if ($profile->whatsapp_enabled)

                        <div class="bp-preview-whatsapp">

                            <i class="fa-brands fa-whatsapp"></i>

                            Message on WhatsApp

                        </div>

                    @endif

                </section>

            </div>

        </div>



        {{-- =========================================================
            SAVE BAR
        ========================================================== --}}

        <div class="bp-save-bar">

            <div>

                <i class="fa-solid fa-circle-info"></i>

                Changes update how buyers see your public business profile.

            </div>


            <button type="submit">

                <i class="fa-solid fa-floppy-disk"></i>

                Save business profile

            </button>

        </div>

    </form>

</div>



{{-- =========================================================
    REMOVE IMAGE MODAL
========================================================== --}}

@if ($profile->profile_image_path)

    <div
        class="bp-modal"
        id="removeBusinessImageModal"
        hidden
    >

        <div
            class="bp-modal-backdrop"
            data-close-delete-image
        ></div>


        <div class="bp-modal-dialog">

            <div class="bp-delete-icon">

                <i class="fa-regular fa-trash-can"></i>

            </div>


            <h2>
                Remove profile picture?
            </h2>


            <p>
                Your business will use its initials until you upload another image.
            </p>


            <div class="bp-modal-actions">

                <button
                    type="button"
                    class="cancel"
                    data-close-delete-image
                >
                    Cancel
                </button>


                <form
                    method="POST"
                    action="{{ route('seller.business-profile.profile-image.destroy') }}"
                >

                    @csrf
                    @method('DELETE')


                    <button
                        type="submit"
                        class="delete"
                    >
                        Remove picture
                    </button>

                </form>

            </div>

        </div>

    </div>

@endif



@push('styles')

<style>

    .bp-page {
        width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .bp-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }


    .bp-eyebrow {
        margin-bottom: 4px;
        color: #12B76A;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }


    .bp-page-header h1 {
        margin: 0;
        color: #101915;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 25px;
        font-weight: 800;
    }


    .bp-page-header p {
        margin: 4px 0 0;
        color: #6E7B74;
        font-size: 9px;
    }


    .bp-preview-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 38px;
        padding: 0 13px;
        border: 1px solid #D9E3DE;
        border-radius: 9px;
        background: #FFFFFF;
        color: #0B3D2E;
        font-size: 8px;
        font-weight: 800;
        text-decoration: none;
    }


    /*
    |--------------------------------------------------------------------------
    | Alert
    |--------------------------------------------------------------------------
    */

    .bp-alert {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 16px;
        padding: 11px 13px;
        border-radius: 10px;
        font-size: 8px;
    }


    .bp-alert-success {
        border: 1px solid #ABEFC6;
        background: #ECFDF3;
        color: #067647;
    }


    .bp-alert-error {
        border: 1px solid #FECDD3;
        background: #FFF1F2;
        color: #B42318;
    }


    .bp-alert-error strong,
    .bp-alert-error span {
        display: block;
    }


    /*
    |--------------------------------------------------------------------------
    | Grid
    |--------------------------------------------------------------------------
    */

    .bp-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(340px, .85fr);
        align-items: start;
        gap: 18px;
    }


    .bp-column {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }


    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .bp-card {
        padding: 21px;
        border: 1px solid #DCE5E0;
        border-radius: 17px;
        background: #FFFFFF;
        box-shadow: 0 10px 30px -25px rgba(11,61,46,.30);
    }


    .bp-card-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 19px;
    }


    .bp-heading-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: #E8F7EF;
        color: #087443;
        font-size: 12px;
    }


    .bp-heading-icon.purple {
        background: #F0ECFF;
        color: #6941C6;
    }


    .bp-heading-icon.whatsapp {
        background: #E7FBEF;
        color: #16A34A;
        font-size: 17px;
    }


    .bp-heading-icon.preview {
        background: #EEF4FF;
        color: #175CD3;
    }


    .bp-card-heading h2 {
        margin: 0;
        color: #101915;
        font-size: 13px;
        font-weight: 800;
    }


    .bp-card-heading p {
        margin: 2px 0 0;
        color: #748079;
        font-size: 8px;
    }


    /*
    |--------------------------------------------------------------------------
    | Profile Image
    |--------------------------------------------------------------------------
    */

    .bp-profile-image-section {
        display: flex;
        align-items: center;
        gap: 16px;
    }


    .bp-profile-image {
        width: 105px;
        height: 105px;
        flex: 0 0 105px;
        overflow: hidden;
        display: grid;
        place-items: center;
        border: 1px solid #D8E4DE;
        border-radius: 22px;
        background: linear-gradient(135deg,#0B3D2E,#12B76A);
        color: white;
        font-size: 27px;
        font-weight: 800;
    }


    .bp-profile-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }


    .bp-profile-image-copy strong {
        display: block;
        color: #26342D;
        font-size: 10px;
    }


    .bp-profile-image-copy p {
        max-width: 330px;
        margin: 4px 0 11px;
        color: #77847D;
        font-size: 8px;
        line-height: 1.55;
    }


    .bp-image-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }


    .bp-upload-button,
    .bp-remove-image {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 11px;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
    }


    .bp-upload-button {
        border: 0;
        background: #0B3D2E;
        color: #FFFFFF;
    }


    .bp-remove-image {
        border: 1px solid #FECDD3;
        background: #FFFFFF;
        color: #D92D20;
    }


    /*
    |--------------------------------------------------------------------------
    | Verified
    |--------------------------------------------------------------------------
    */

    .bp-divider {
        height: 1px;
        margin: 18px 0;
        background: #E8ECEA;
    }


    .bp-verified-box {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
        padding: 11px;
        border: 1px solid #ABEFC6;
        border-radius: 11px;
        background: #F2FCF6;
    }


    .bp-verified-icon {
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        background: #D1FADF;
        color: #067647;
    }


    .bp-verified-box > div:nth-child(2) {
        min-width: 0;
        flex: 1;
    }


    .bp-verified-box strong {
        display: block;
        color: #05603A;
        font-size: 9px;
    }


    .bp-verified-box p {
        margin: 2px 0 0;
        color: #498269;
        font-size: 7px;
        line-height: 1.5;
    }


    .bp-verified-box > span {
        padding: 4px 7px;
        border-radius: 999px;
        background: #D1FADF;
        color: #067647;
        font-size: 7px;
        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .bp-two-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 11px;
    }


    .bp-field {
        margin-bottom: 14px;
    }


    .bp-field label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
        color: #26342D;
        font-size: 9px;
        font-weight: 700;
    }


    .bp-field label small {
        color: #8A9690;
        font-size: 7px;
        font-weight: 500;
    }


    .bp-field input,
    .bp-field textarea {
        width: 100%;
        border: 1px solid #DCE5E0;
        border-radius: 10px;
        background: #FFFFFF;
        color: #101915;
        font-family: inherit;
        font-size: 9px;
        outline: none;
    }


    .bp-field input {
        height: 43px;
        padding: 0 12px;
    }


    .bp-field textarea {
        min-height: 110px;
        padding: 11px 12px;
        resize: vertical;
        line-height: 1.65;
    }


    .bp-field input:focus,
    .bp-field textarea:focus {
        border-color: #12B76A;
        box-shadow: 0 0 0 3px rgba(18,183,106,.08);
    }


    .bp-readonly {
        min-height: 43px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0 11px;
        border: 1px solid #E2E8E5;
        border-radius: 10px;
        background: #F7F9F8;
    }


    .bp-readonly i {
        color: #7D8983;
        font-size: 9px;
    }


    .bp-readonly span {
        min-width: 0;
        flex: 1;
        overflow: hidden;
        color: #4C5952;
        font-size: 9px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    .bp-readonly small {
        color: #97A19C;
        font-size: 7px;
    }


    .bp-input-icon {
        position: relative;
    }


    .bp-input-icon > i {
        position: absolute;
        left: 12px;
        top: 50%;
        z-index: 2;
        color: #87938D;
        font-size: 9px;
        transform: translateY(-50%);
    }


    .bp-input-icon input {
        padding-left: 34px;
    }


    .bp-textarea-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 5px;
        color: #8A9690;
        font-size: 7px;
    }


    .bp-textarea-footer strong {
        flex: none;
        color: #67746D;
    }


    .bp-help {
        margin-top: 5px;
        color: #8A9690;
        font-size: 7px;
        line-height: 1.45;
    }


    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    .bp-public-email {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 11px;
        border: 1px solid #E4EAE7;
        border-radius: 10px;
        background: #F8FAF9;
    }


    .bp-public-email span:first-child {
        display: block;
        margin-bottom: 2px;
        color: #859089;
        font-size: 7px;
    }


    .bp-public-email strong {
        color: #344139;
        font-size: 9px;
    }


    .bp-email-verified {
        color: #087443;
        font-size: 7px;
        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle
    |--------------------------------------------------------------------------
    */

    .bp-mini-title {
        margin: 0 0 4px;
        color: #344139;
        font-size: 9px;
    }


    .bp-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 0;
        border-bottom: 1px solid #EDF1EF;
        cursor: pointer;
    }


    .bp-toggle-row strong {
        display: block;
        color: #344139;
        font-size: 9px;
    }


    .bp-toggle-row > div > span {
        display: block;
        margin-top: 2px;
        color: #818D87;
        font-size: 7px;
    }


    .bp-switch {
        position: relative;
        width: 38px;
        height: 22px;
        flex: 0 0 38px;
    }


    .bp-switch input {
        position: absolute;
        opacity: 0;
    }


    .bp-switch > span {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #DCE3DF;
        transition: .18s;
    }


    .bp-switch > span::after {
        content: '';
        position: absolute;
        left: 3px;
        top: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #FFFFFF;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
        transition: .18s;
    }


    .bp-switch input:checked + span {
        background: #12B76A;
    }


    .bp-switch input:checked + span::after {
        transform: translateX(16px);
    }


    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    */

    .bp-whatsapp-card {
        border-color: #C9ECD8;
    }


    .bp-connected-badge {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 8px;
        border-radius: 999px;
        background: #E7FBEF;
        color: #15803D;
        font-size: 7px;
        font-weight: 800;
    }


    .bp-connected-badge i {
        font-size: 5px;
    }


    .bp-whatsapp-info {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
        padding: 12px;
        border-radius: 11px;
        background: #F0FFF6;
    }


    .bp-whatsapp-info > i {
        margin-top: 1px;
        color: #16A34A;
        font-size: 22px;
    }


    .bp-whatsapp-info strong {
        color: #166534;
        font-size: 9px;
    }


    .bp-whatsapp-info p {
        margin: 3px 0 0;
        color: #548164;
        font-size: 7px;
        line-height: 1.5;
    }


    .bp-whatsapp-toggle {
        margin-bottom: 14px;
    }


    .bp-test-whatsapp {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid #AFE3C3;
        border-radius: 9px;
        background: #F0FFF6;
        color: #15803D;
        font-size: 8px;
        font-weight: 800;
        text-decoration: none;
    }


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    .bp-public-preview {
        display: flex;
        align-items: center;
        gap: 11px;
    }


    .bp-preview-avatar {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        overflow: hidden;
        display: grid;
        place-items: center;
        border-radius: 15px;
        background: linear-gradient(135deg,#0B3D2E,#12B76A);
        color: #FFFFFF;
        font-size: 16px;
        font-weight: 800;
    }


    .bp-preview-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }


    .bp-preview-info {
        min-width: 0;
        flex: 1;
    }


    .bp-preview-info > div {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
    }


    .bp-preview-info strong {
        color: #101915;
        font-size: 11px;
    }


    .bp-preview-verified {
        padding: 3px 6px;
        border-radius: 999px;
        background: #ECFDF3;
        color: #067647;
        font-size: 6px;
        font-weight: 800;
    }


    .bp-preview-info p {
        margin: 2px 0;
        color: #718078;
        font-size: 8px;
    }


    .bp-preview-info > span {
        display: block;
        overflow: hidden;
        color: #606D66;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    .bp-preview-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 13px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #E7ECE9;
        color: #718078;
        font-size: 7px;
    }


    .bp-preview-meta i {
        color: #12B76A;
    }


    .bp-preview-whatsapp {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin-top: 12px;
        min-height: 35px;
        border-radius: 8px;
        background: #16A34A;
        color: #FFFFFF;
        font-size: 8px;
        font-weight: 800;
    }


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    .bp-save-bar {
        position: sticky;
        bottom: 14px;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 18px;
        padding: 11px 13px;
        border: 1px solid #D8E2DD;
        border-radius: 13px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 15px 45px -25px rgba(11,61,46,.5);
        backdrop-filter: blur(12px);
    }


    .bp-save-bar > div {
        color: #6F7C75;
        font-size: 8px;
    }


    .bp-save-bar > div i {
        margin-right: 4px;
        color: #12B76A;
    }


    .bp-save-bar button {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 15px;
        border: 0;
        border-radius: 9px;
        background: #0B3D2E;
        color: #FFFFFF;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Modal
    |--------------------------------------------------------------------------
    */

    .bp-modal[hidden] {
        display: none !important;
    }


    .bp-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: grid;
        place-items: center;
        padding: 15px;
    }


    .bp-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(10,25,18,.58);
        backdrop-filter: blur(4px);
    }


    .bp-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(390px, 100%);
        padding: 23px;
        border-radius: 17px;
        background: #FFFFFF;
        text-align: center;
        box-shadow: 0 30px 80px rgba(0,0,0,.25);
    }


    .bp-delete-icon {
        width: 47px;
        height: 47px;
        display: grid;
        place-items: center;
        margin: 0 auto 12px;
        border-radius: 13px;
        background: #FFF1F2;
        color: #D92D20;
        font-size: 16px;
    }


    .bp-modal-dialog h2 {
        margin: 0;
        font-size: 15px;
    }


    .bp-modal-dialog > p {
        margin: 6px 0 18px;
        color: #718078;
        font-size: 8px;
        line-height: 1.5;
    }


    .bp-modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }


    .bp-modal-actions form {
        margin: 0;
    }


    .bp-modal-actions button {
        width: 100%;
        min-height: 38px;
        border-radius: 9px;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
    }


    .bp-modal-actions .cancel {
        border: 1px solid #DCE5E0;
        background: #FFFFFF;
        color: #445149;
    }


    .bp-modal-actions .delete {
        border: 0;
        background: #D92D20;
        color: #FFFFFF;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media(max-width: 900px) {

        .bp-grid {
            grid-template-columns: 1fr;
        }

    }


    @media(max-width: 600px) {

        .bp-page-header {
            flex-direction: column;
        }


        .bp-profile-image-section {
            align-items: flex-start;
            flex-direction: column;
        }


        .bp-two-fields {
            grid-template-columns: 1fr;
        }


        .bp-save-bar {
            align-items: stretch;
            flex-direction: column;
        }


        .bp-save-bar button {
            width: 100%;
        }

    }

</style>

@endpush



@push('scripts')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | Image Preview
        |--------------------------------------------------------------------------
        */

        const imageInput =
            document.getElementById(
                'businessProfileImageInput'
            );


        const previewImage =
            document.getElementById(
                'businessProfilePreviewImage'
            );


        const previewInitials =
            document.getElementById(
                'businessProfilePreviewInitials'
            );


        if (
            imageInput
            &&
            previewImage
        ) {

            imageInput.addEventListener(
                'change',
                function () {

                    const file =
                        this.files
                        &&
                        this.files[0];


                    if (!file) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Basic Client Validation
                    |--------------------------------------------------------------------------
                    */

                    const allowedTypes = [

                        'image/jpeg',

                        'image/png',

                        'image/webp',

                    ];


                    if (
                        !allowedTypes.includes(
                            file.type
                        )
                    ) {

                        alert(
                            'Please select a JPG, PNG or WEBP image.'
                        );


                        imageInput.value =
                            '';


                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | 3MB
                    |--------------------------------------------------------------------------
                    */

                    if (
                        file.size
                        >
                        3 * 1024 * 1024
                    ) {

                        alert(
                            'The profile picture must be 3 MB or smaller.'
                        );


                        imageInput.value =
                            '';


                        return;
                    }


                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            previewImage.src =
                                event.target.result;


                            previewImage.hidden =
                                false;


                            if (
                                previewInitials
                            ) {

                                previewInitials.hidden =
                                    true;
                            }

                        };


                    reader.readAsDataURL(
                        file
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | About Character Counter
        |--------------------------------------------------------------------------
        */

        const about =
            document.getElementById(
                'businessAbout'
            );


        const aboutCounter =
            document.getElementById(
                'businessAboutCount'
            );


        function updateAboutCounter()
        {
            if (
                !about
                ||
                !aboutCounter
            ) {
                return;
            }


            aboutCounter.textContent =
                about.value.length
                +
                ' / 3000';
        }


        if (about) {

            about.addEventListener(
                'input',
                updateAboutCounter
            );


            updateAboutCounter();
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Image Modal
        |--------------------------------------------------------------------------
        */

        const deleteModal =
            document.getElementById(
                'removeBusinessImageModal'
            );


        const openDelete =
            document.querySelector(
                '[data-open-delete-image]'
            );


        function closeDeleteModal()
        {
            if (!deleteModal) {
                return;
            }


            deleteModal.hidden =
                true;


            document.body.style.overflow =
                '';
        }


        if (
            deleteModal
            &&
            openDelete
        ) {

            openDelete.addEventListener(
                'click',
                function () {

                    deleteModal.hidden =
                        false;


                    document.body.style.overflow =
                        'hidden';
                }
            );


            document
                .querySelectorAll(
                    '[data-close-delete-image]'
                )
                .forEach(
                    function (button) {

                        button.addEventListener(
                            'click',
                            closeDeleteModal
                        );
                    }
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Escape
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

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


@endsection