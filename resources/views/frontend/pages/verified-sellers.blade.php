@extends('frontend.layouts.app')

@section('title', 'Become a Verified Seller | MidPoint')

@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div class="mp-wrap">

            {{-- Heading --}}
            <div
                class="mp-section-head
                       mx-auto text-center"
            >

                <div class="mp-eyebrow justify-center">
                    Verified sellers
                </div>

                <h1>
                    Get featured. List products. Sell on sight.
                </h1>

                <p>
                    Verified sellers appear on Featured Businesses with
                    listed products, so buyers can start a secure transaction
                    with one click — no back-and-forth.
                </p>

            </div>


            {{-- Steps --}}
            <div class="mp-grid-3 mb-11">

                @foreach ([
                    [
                        'number' => '1',
                        'title' => 'Apply & verify',
                        'text' => 'Submit your business name, CAC or BVN details, and links to your existing store (Instagram, WhatsApp, Jiji). Verification takes 1–2 business days.'
                    ],
                    [
                        'number' => '2',
                        'title' => 'Pick a package',
                        'text' => 'Choose Starter, Standard or Premium — your package sets how many products you can list on your profile.'
                    ],
                    [
                        'number' => '3',
                        'title' => 'List & get buyers',
                        'text' => 'Add products with price and delivery mode. Buyers click "Start secure transaction", pick a product, and pay into escrow instantly.'
                    ]
                ] as $step)

                    <div class="mp-card p-[26px]">

                        <div
                            class="mb-4 grid
                                   h-[42px] w-[42px]
                                   place-items-center
                                   rounded-[13px]
                                   bg-[#0B3D2E]
                                   font-['Bricolage_Grotesque']
                                   text-[18px]
                                   font-extrabold
                                   text-[#7EF0B6]"
                        >
                            {{ $step['number'] }}
                        </div>


                        <h2
                            class="mb-2
                                   font-['Bricolage_Grotesque']
                                   text-[18px] font-bold"
                        >
                            {{ $step['title'] }}
                        </h2>


                        <p class="mp-muted text-[14px]">
                            {{ $step['text'] }}
                        </p>

                    </div>

                @endforeach

            </div>


            {{-- Packages --}}
            <div class="mp-grid-3">

                {{-- Starter --}}
                <div
                    class="mp-card
                           flex flex-col
                           p-7"
                >

                    <span class="mp-badge mp-badge-slate">
                        Starter
                    </span>


                    <div
                        class="my-[14px] mb-0
                               font-['Bricolage_Grotesque']
                               text-[38px]
                               font-extrabold"
                    >
                        ₦5,000

                        <span
                            class="text-[15px]
                                   font-semibold
                                   text-[#5A6660]"
                        >
                            /month
                        </span>

                    </div>


                    <div class="mp-small mp-muted mb-4">
                        For new sellers testing the waters
                    </div>


                    <div
                        class="mp-small mb-5
                               flex flex-col gap-[9px]"
                    >
                        <div>✅ Up to <strong>4 listed products</strong></div>
                        <div>✅ Verified badge & Featured listing</div>
                        <div>✅ Trust score on your profile</div>
                        <div>✅ Buyer reviews</div>
                    </div>


                    <button
                        type="button"
                        data-package="Starter"
                        data-price="₦5,000/month"
                        class="verified-package-btn
                               mp-btn mp-btn-outline mt-auto"
                    >
                        Choose Starter
                    </button>

                </div>


                {{-- Standard --}}
                <div
                    class="mp-card
                           relative flex flex-col
                           border-2 !border-[#12B76A]
                           p-7"
                >

                    <span
                        class="absolute -top-3
                               left-1/2
                               -translate-x-1/2
                               whitespace-nowrap
                               mp-badge mp-badge-green"
                    >
                        Most popular
                    </span>


                    <span class="mp-badge mp-badge-green">
                        Standard
                    </span>


                    <div
                        class="my-[14px] mb-0
                               font-['Bricolage_Grotesque']
                               text-[38px]
                               font-extrabold"
                    >
                        ₦10,000

                        <span
                            class="text-[15px]
                                   font-semibold
                                   text-[#5A6660]"
                        >
                            /month
                        </span>

                    </div>


                    <div class="mp-small mp-muted mb-4">
                        For growing businesses
                    </div>


                    <div
                        class="mp-small mb-5
                               flex flex-col gap-[9px]"
                    >
                        <div>✅ Up to <strong>10 listed products</strong></div>
                        <div>✅ Everything in Starter</div>
                        <div>✅ Priority placement in Featured Businesses</div>
                        <div>✅ Faster support response</div>
                    </div>


                    <button
                        type="button"
                        data-package="Standard"
                        data-price="₦10,000/month"
                        class="verified-package-btn
                               mp-btn mp-btn-green mt-auto"
                    >
                        Choose Standard
                    </button>

                </div>


                {{-- Premium --}}
                <div
                    class="mp-card
                           flex flex-col
                           p-7"
                >

                    <span class="mp-badge mp-badge-purple">
                        Premium
                    </span>


                    <div
                        class="my-[14px] mb-0
                               font-['Bricolage_Grotesque']
                               text-[38px]
                               font-extrabold"
                    >
                        ₦25,000

                        <span
                            class="text-[15px]
                                   font-semibold
                                   text-[#5A6660]"
                        >
                            /month
                        </span>

                    </div>


                    <div class="mp-small mp-muted mb-4">
                        For high-volume sellers
                    </div>


                    <div
                        class="mp-small mb-5
                               flex flex-col gap-[9px]"
                    >
                        <div>✅ Up to <strong>30 listed products</strong></div>
                        <div>✅ Everything in Standard</div>
                        <div>✅ Homepage spotlight rotation</div>
                        <div>✅ Dedicated account manager</div>
                    </div>


                    <button
                        type="button"
                        data-package="Premium"
                        data-price="₦25,000/month"
                        class="verified-package-btn
                               mp-btn mp-btn-purple mt-auto"
                    >
                        Choose Premium
                    </button>

                </div>

            </div>


            {{-- Application --}}
            <div
                id="verified-application"
                class="mp-card
                       mx-auto mt-[34px]
                       max-w-[720px]
                       p-[30px]"
            >

                <h2
                    class="font-['Bricolage_Grotesque']
                           text-[18px]
                           font-bold"
                >
                    Apply to become a Verified Seller
                </h2>


                <div class="mp-small mp-muted mb-[18px]">

                    Selected package:

                    <span
                        id="selected-package"
                        class="mp-badge
                               mp-badge-green
                               ml-1"
                    >
                        Standard · ₦10,000/month
                    </span>

                </div>


                <form
                    id="verified-seller-form"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="package"
                        id="selected-package-input"
                        value="Standard"
                    >


                    <div class="mp-grid-2">

                        <div class="mp-field">

                            <label>
                                Business name
                            </label>

                            <input
                                type="text"
                                name="business_name"
                                placeholder="e.g. Temi Gadgets"
                                required
                            >

                        </div>


                        <div class="mp-field">

                            <label>
                                Category
                            </label>

                            <select
                                name="category"
                                required
                            >
                                <option>Phones & Electronics</option>
                                <option>Beauty & Hair</option>
                                <option>Fashion & Tailoring</option>
                                <option>Home & Kitchen</option>
                                <option>Power & Tools</option>
                                <option>Books & Stationery</option>
                                <option>Other</option>
                            </select>

                        </div>


                        <div class="mp-field">

                            <label>
                                Location
                            </label>

                            <input
                                type="text"
                                name="location"
                                placeholder="e.g. Ikeja, Lagos"
                                required
                            >

                        </div>


                        <div class="mp-field">

                            <label>
                                Phone / WhatsApp
                            </label>

                            <input
                                type="tel"
                                name="phone"
                                placeholder="0803 xxx xxxx"
                                required
                            >

                        </div>


                        <div class="mp-field">

                            <label>
                                CAC number (or BVN for individuals)
                            </label>

                            <input
                                type="text"
                                name="cac_or_bvn"
                                placeholder="RC1234567"
                                required
                            >

                        </div>


                        <div class="mp-field">

                            <label>
                                Existing store link
                            </label>

                            <input
                                type="url"
                                name="store_link"
                                placeholder="Instagram / Jiji / WhatsApp catalogue link"
                            >

                        </div>

                    </div>


                    <div class="mp-field">

                        <label>
                            Short business description
                        </label>

                        <textarea
                            name="description"
                            rows="3"
                            placeholder="What do you sell, and what makes buyers trust you?"
                            required
                        ></textarea>

                    </div>


                    <div class="mp-field">

                        <label>
                            Upload verification documents
                        </label>


                        <input
                            id="verification-documents"
                            type="file"
                            name="documents[]"
                            multiple
                            class="hidden"
                            accept=".jpg,.jpeg,.png,.pdf"
                        >


                        <label
                            for="verification-documents"
                            class="mp-upload !mb-0"
                        >
                            📎 CAC certificate, valid ID, or utility bill ·

                            <strong class="text-[#12B76A]">
                                browse
                            </strong>

                            <div
                                id="verification-file-name"
                                class="mt-2 text-xs"
                            ></div>
                        </label>

                    </div>


                    <button
                        type="submit"
                        class="mp-btn
                               mp-btn-primary
                               mp-btn-lg
                               w-full"
                    >
                        Submit application
                    </button>


                    <p
                        id="verified-form-success"
                        class="mt-3 hidden
                               rounded-xl bg-[#E8F7EF]
                               p-3 text-center
                               text-[13px]
                               font-semibold
                               text-[#0E7A4C]"
                    >
                        Application captured successfully.
                        Backend submission will be connected in the
                        seller-verification module.
                    </p>


                    <p class="mp-small mp-muted mt-3 text-center">
                        You'll only be billed after your business is approved.
                    </p>

                </form>

            </div>

        </div>

    </section>

</div>


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.verified-package-btn');

    const label = document.getElementById('selected-package');

    const input = document.getElementById('selected-package-input');

    const application = document.getElementById('verified-application');


    buttons.forEach(function (button) {

        button.addEventListener('click', function () {

            const packageName = button.dataset.package;

            const packagePrice = button.dataset.price;


            input.value = packageName;

            label.textContent =
                packageName + ' · ' + packagePrice;


            label.className = 'mp-badge ml-1';


            if (packageName === 'Premium') {

                label.classList.add('mp-badge-purple');

            } else if (packageName === 'Starter') {

                label.classList.add('mp-badge-slate');

            } else {

                label.classList.add('mp-badge-green');

            }


            application.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });

        });

    });


    const fileInput =
        document.getElementById('verification-documents');

    const fileName =
        document.getElementById('verification-file-name');


    fileInput?.addEventListener('change', function () {

        const count = this.files.length;

        fileName.textContent =
            count
                ? count + ' file(s) selected'
                : '';

    });


    document
        .getElementById('verified-seller-form')
        ?.addEventListener('submit', function (event) {

            event.preventDefault();

            document
                .getElementById('verified-form-success')
                ?.classList.remove('hidden');

        });

});

</script>

@endpush

@endsection