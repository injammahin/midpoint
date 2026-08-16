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

    .about-dynamic {
        background: #F6F9F7;
    }


    .about-eyebrow {
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


    .about-eyebrow::before {
        content: '';
        width: 22px;
        height: 2px;
        border-radius: 99px;
        background: #12B76A;
    }


    .about-stat {
        padding: 21px;
        border: 1px solid #E4EAE6;
        border-radius: 18px;
        background: #FFFFFF;
        box-shadow:
            0 6px 24px -8px
            rgba(11, 61, 46, .12);
    }


    .about-stat-label {
        color: #5A6660;
        font-size: 12px;
        font-weight: 700;
    }


    .about-stat-value {
        margin: 7px 0 3px;
        color: #0B3D2E;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 23px;
        font-weight: 800;
    }


    .about-stat-description {
        color: #718078;
        font-size: 12px;
    }


    .about-principle {
        height: 100%;
        padding: 21px;
        border: 1px solid #E4EAE6;
        border-radius: 17px;
        background: #FFFFFF;
    }


    .about-principle-icon {
        width: 43px;
        height: 43px;
        display: grid;
        place-items: center;
        margin-bottom: 14px;
        border-radius: 13px;
        background: #E8F7EF;
        font-size: 19px;
    }


    .about-principle h3 {
        margin-bottom: 7px;
        color: #0D120F;
        font-family: 'Bricolage Grotesque', sans-serif;
        font-size: 16px;
        font-weight: 800;
    }


    .about-principle p {
        color: #5A6660;
        font-size: 13px;
        line-height: 1.65;
    }


    .about-cta {
        overflow: hidden;
        padding: 43px 26px;
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


<div class="about-dynamic">


    {{-- =========================================================
        INTRODUCTION
    ========================================================== --}}

    <section
        class="
            px-[22px]
            py-[58px]
            sm:py-[78px]
        "
    >


        <div
            class="
                mx-auto
                max-w-[860px]
            "
        >


            <div class="about-eyebrow">

                {{
                    data_get(
                        $content,
                        'hero.eyebrow'
                    )
                }}

            </div>



            <h1
                class="
                    max-w-[820px]
                    font-['Bricolage_Grotesque']
                    text-[31px]
                    font-extrabold
                    leading-[1.12]
                    tracking-[-0.02em]
                    text-[#0D120F]
                    sm:text-[39px]
                    lg:text-[43px]
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
                    mt-[18px]
                    max-w-[810px]
                    text-[15px]
                    leading-[1.75]
                    text-[#5A6660]
                    sm:text-[16.5px]
                "
            >

                {{
                    data_get(
                        $content,
                        'hero.description'
                    )
                }}

            </p>



            {{-- =================================================
                STATS
            ================================================== --}}

            @if(count($content['stats'] ?? []))


                <div
                    class="
                        mt-[38px]
                        grid
                        grid-cols-1
                        gap-4
                        sm:grid-cols-2
                        lg:grid-cols-3
                    "
                >


                    @foreach (($content['stats'] ?? []) as $stat)


                        <article class="about-stat">


                            <div class="about-stat-label">

                                {{ $stat['label'] ?? '' }}

                            </div>


                            <div class="about-stat-value">

                                {{ $stat['value'] ?? '' }}

                            </div>


                            @if(!empty($stat['description']))

                                <div class="about-stat-description">

                                    {{ $stat['description'] }}

                                </div>

                            @endif


                        </article>


                    @endforeach


                </div>


            @endif


        </div>


    </section>



    {{-- =========================================================
        PRINCIPLES
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


            <div class="about-eyebrow">
                MidPoint
            </div>


            <h2
                class="
                    font-['Bricolage_Grotesque']
                    text-[27px]
                    font-extrabold
                    text-[#0D120F]
                    sm:text-[34px]
                "
            >

                {{
                    $content['principles_heading']
                    ??
                    ''
                }}

            </h2>



            @if(count($content['principles'] ?? []))


                <div
                    class="
                        mt-[30px]
                        grid
                        grid-cols-1
                        gap-4
                        sm:grid-cols-2
                        lg:grid-cols-4
                    "
                >


                    @foreach (($content['principles'] ?? []) as $principle)


                        <article class="about-principle">


                            @if(!empty($principle['icon']))

                                <div class="about-principle-icon">

                                    {{ $principle['icon'] }}

                                </div>

                            @endif


                            <h3>

                                {{ $principle['title'] ?? '' }}

                            </h3>


                            <p>

                                {{ $principle['description'] ?? '' }}

                            </p>


                        </article>


                    @endforeach


                </div>


            @endif


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
                about-cta
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
                    max-w-[560px]
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