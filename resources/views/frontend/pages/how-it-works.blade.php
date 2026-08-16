@extends('frontend.layouts.app')


@section(
    'title',
    $page->meta_title
)


@section(
    'meta_description',
    $page->meta_description
)


@push('styles')

<style>

    .hiw-page {
        background: #F6F9F7;
    }


    .hiw-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        color: #12B76A;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }


    .hiw-eyebrow::before {
        content: '';
        width: 22px;
        height: 2px;
        border-radius: 99px;
        background: #12B76A;
    }


    .hiw-journey-card {
        height: 100%;
        padding: 27px;
        border: 1px solid #E4EAE6;
        border-radius: 20px;
        background: #FFFFFF;
        box-shadow:
            0 7px 26px -10px
            rgba(11, 61, 46, .16);
    }


    .hiw-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 11px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
    }


    .hiw-badge.seller {
        color: #0E7A4C;
        background: #E8F7EF;
    }


    .hiw-badge.buyer {
        color: #6544E4;
        background: #F1EDFE;
    }


    .hiw-timeline {
        position: relative;
        margin-top: 22px;
        padding-left: 34px;
    }


    .hiw-timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        border-radius: 99px;
        background: #E4EAE6;
    }


    .hiw-step {
        position: relative;
        padding-bottom: 25px;
    }


    .hiw-step:last-child {
        padding-bottom: 0;
    }


    .hiw-step-number {
        position: absolute;
        left: -34px;
        top: -1px;
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border: 2px solid #12B76A;
        border-radius: 50%;
        color: #FFFFFF;
        background: #12B76A;
        font-size: 10px;
        font-weight: 800;
    }


    .hiw-journey-card.buyer
    .hiw-step-number {
        border-color: #7A5AF8;
        background: #7A5AF8;
    }


    .hiw-step h3 {
        color: #172019;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 15px;
        font-weight: 800;
    }


    .hiw-step p {
        margin-top: 4px;
        color: #5A6660;
        font-size: 12.5px;
        line-height: 1.65;
    }


    .hiw-delivery-card {
        height: 100%;
        padding: 21px;
        border: 1px solid #D8E6DE;
        border-radius: 17px;
        background: #FFFFFF;
    }


    .hiw-delivery-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: #E8F7EF;
        font-size: 20px;
    }


    .hiw-small-badge {
        display: inline-flex;
        margin-left: 6px;
        padding: 3px 8px;
        border-radius: 99px;
        color: #6544E4;
        background: #F1EDFE;
        font-size: 9px;
        font-weight: 800;
    }


    .hiw-cta {
        padding: 43px 25px;
        border-radius: 22px;
        color: #FFFFFF;
        text-align: center;
        background:
            linear-gradient(
                120deg,
                #0B3D2E,
                #7A5AF8
            );
    }

</style>

@endpush



@section('content')


<div class="hiw-page">


    {{-- =========================================================
        HEADER
    ========================================================== --}}

    <section
        class="
            px-[22px]
            py-[55px]
            sm:py-[72px]
        "
    >


        <div
            class="
                mx-auto
                max-w-[1160px]
            "
        >


            <div
                class="
                    max-w-[680px]
                "
            >


                <div class="hiw-eyebrow">

                    {{
                        data_get(
                            $content,
                            'hero.eyebrow'
                        )
                    }}

                </div>


                <h1
                    class="
                        font-['Bricolage_Grotesque']
                        text-[31px]
                        font-extrabold
                        leading-[1.12]
                        text-[#0D120F]
                        sm:text-[40px]
                    "
                >

                    {{
                        data_get(
                            $content,
                            'hero.title'
                        )
                    }}

                </h1>


                <p
                    class="
                        mt-[12px]
                        text-[15px]
                        leading-[1.7]
                        text-[#5A6660]
                        sm:text-[16px]
                    "
                >

                    {{
                        data_get(
                            $content,
                            'hero.description'
                        )
                    }}

                </p>


            </div>


        </div>


    </section>



    {{-- =========================================================
        SELLER + BUYER JOURNEY
    ========================================================== --}}

    <section
        class="
            px-[22px]
            pb-[58px]
            sm:pb-[72px]
        "
    >


        <div
            class="
                mx-auto
                grid
                max-w-[1160px]
                grid-cols-1
                gap-5
                lg:grid-cols-2
            "
        >


            {{-- Seller --}}

            <article class="hiw-journey-card">


                <span class="hiw-badge seller">

                    {{
                        $content['seller_badge']
                        ??
                        'For sellers'
                    }}

                </span>



                <div class="hiw-timeline">


                    @foreach (($content['seller_steps'] ?? []) as $step)


                        <div class="hiw-step">


                            <span class="hiw-step-number">

                                {{ $loop->iteration }}

                            </span>


                            <h3>

                                {{ $step['title'] ?? '' }}

                            </h3>


                            <p>

                                {{ $step['description'] ?? '' }}

                            </p>


                        </div>


                    @endforeach


                </div>


            </article>



            {{-- Buyer --}}

            <article
                class="
                    hiw-journey-card
                    buyer
                "
            >


                <span class="hiw-badge buyer">

                    {{
                        $content['buyer_badge']
                        ??
                        'For buyers'
                    }}

                </span>



                <div class="hiw-timeline">


                    @foreach (($content['buyer_steps'] ?? []) as $step)


                        <div class="hiw-step">


                            <span class="hiw-step-number">

                                {{ $loop->iteration }}

                            </span>


                            <h3>

                                {{ $step['title'] ?? '' }}

                            </h3>


                            <p>

                                {{ $step['description'] ?? '' }}

                            </p>


                        </div>


                    @endforeach


                </div>


            </article>


        </div>


    </section>



    {{-- =========================================================
        DELIVERY
    ========================================================== --}}

    <section
        class="
            border-y
            border-[#E4EAE6]
            bg-white
            px-[22px]
            py-[54px]
            sm:py-[68px]
        "
    >


        <div
            class="
                mx-auto
                max-w-[1160px]
            "
        >


            <div class="hiw-eyebrow">
                MidPoint
            </div>


            <h2
                class="
                    font-['Bricolage_Grotesque']
                    text-[28px]
                    font-extrabold
                    text-[#0D120F]
                    sm:text-[34px]
                "
            >

                {{
                    $content['delivery_heading']
                    ??
                    'Delivery'
                }}

            </h2>



            <div
                class="
                    mt-[27px]
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-2
                "
            >


                @foreach (($content['delivery_cards'] ?? []) as $card)


                    <article class="hiw-delivery-card">


                        @if(!empty($card['icon']))

                            <div class="hiw-delivery-icon">

                                {{ $card['icon'] }}

                            </div>

                        @endif



                        <h3
                            class="
                                mt-[14px]
                                font-['Bricolage_Grotesque']
                                text-[16px]
                                font-extrabold
                                text-[#172019]
                            "
                        >

                            {{ $card['title'] ?? '' }}


                            @if(!empty($card['badge']))

                                <span class="hiw-small-badge">

                                    {{ $card['badge'] }}

                                </span>

                            @endif


                        </h3>



                        <p
                            class="
                                mt-[7px]
                                text-[13px]
                                leading-[1.65]
                                text-[#5A6660]
                            "
                        >

                            {{ $card['description'] ?? '' }}

                        </p>


                    </article>


                @endforeach


            </div>


        </div>


    </section>



    {{-- =========================================================
        CTA
    ========================================================== --}}

    <section
        class="
            px-[22px]
            py-[52px]
            sm:py-[70px]
        "
    >


        <div
            class="
                hiw-cta
                mx-auto
                max-w-[1160px]
            "
        >


            <h2
                class="
                    font-['Bricolage_Grotesque']
                    text-[26px]
                    font-extrabold
                    sm:text-[34px]
                "
            >

                {{
                    data_get(
                        $content,
                        'cta.title'
                    )
                }}

            </h2>



            <p
                class="
                    mx-auto
                    mt-[11px]
                    max-w-[570px]
                    text-[14px]
                    leading-[1.65]
                    text-[#E5DFFC]
                "
            >

                {{
                    data_get(
                        $content,
                        'cta.description'
                    )
                }}

            </p>



            <a
                href="{{
                    data_get(
                        $content,
                        'cta.button_url',
                        '/register'
                    )
                }}"

                class="
                    mt-[22px]
                    inline-flex
                    min-h-[48px]
                    items-center
                    justify-center
                    rounded-[13px]
                    bg-white
                    px-6
                    text-[14px]
                    font-bold
                    text-[#0B3D2E]
                    transition
                    hover:-translate-y-px
                "
            >

                {{
                    data_get(
                        $content,
                        'cta.button_text'
                    )
                }}

            </a>


        </div>


    </section>


</div>


@endsection