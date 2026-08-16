@extends('frontend.layouts.app')


@section(
    'title',
    'FAQs | Midpoint'
)


@section(
    'meta_description',
    'Frequently asked questions about Midpoint transactions, payments, delivery, inspections, disputes and seller payouts.'
)


@section('content')

<div class="mp-page">

    <section class="mp-section">

        <div
            class="mp-wrap
                   !max-w-[760px]"
        >

            {{-- =========================================================
                HEADING
            ========================================================== --}}
            <div class="mp-section-head">

                <div class="mp-eyebrow">
                    FAQs
                </div>


                <h1>
                    Everything you might want to know.
                </h1>

            </div>


            {{-- =========================================================
                FAQ LIST
            ========================================================== --}}
            <div class="home-faq">

                @forelse ($faqs as $faq)

                    <details class="mp-faq">

                        <summary>

                            <span>
                                {{ $faq->question }}
                            </span>

                        </summary>


                        <div class="mp-faq-answer">

                            {!! nl2br(
                                e(
                                    $faq->answer
                                )
                            ) !!}

                        </div>

                    </details>

                @empty

                    <div
                        class="mp-card
                               px-6 py-12
                               text-center"
                    >

                        <div
                            class="mx-auto
                                   grid h-14 w-14
                                   place-items-center
                                   rounded-full
                                   bg-[#E8F7EF]
                                   text-[#12B76A]"
                        >

                            <i class="fa-regular fa-circle-question"></i>

                        </div>


                        <h2
                            class="mt-4
                                   font-['Bricolage_Grotesque']
                                   text-[18px]
                                   font-bold"
                        >
                            No FAQs available
                        </h2>


                        <p
                            class="mt-2
                                   text-[13px]
                                   text-[#5A6660]"
                        >
                            Please check again later or contact
                            our support team.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- =========================================================
                SUPPORT
            ========================================================== --}}
            <div
                class="mp-card
                       mt-6
                       flex flex-col
                       gap-[14px]
                       p-6
                       sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >

                <div>

                    <strong>
                        Still have a question?
                    </strong>


                    <div
                        class="mp-small
                               mp-muted"
                    >
                        Our support team is here to help.
                    </div>

                </div>


                <a
                    href="{{ route('support') }}"
                    class="mp-btn
                           mp-btn-primary
                           shrink-0"
                >
                    Visit Support Centre
                </a>

            </div>

        </div>

    </section>

</div>

@endsection