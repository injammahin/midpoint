@extends('frontend.layouts.app')


@section(
    'title',
    'Thank You | MidPoint'
)


@section('content')

<div class="mp-page">

    <section
        class="flex min-h-[560px]
               items-center
               bg-[#F6F9F7]
               py-[70px]"
    >

        <div
            class="mx-auto
                   w-full
                   max-w-[620px]
                   px-[22px]"
        >

            <div
                class="mp-card
                       px-6 py-10
                       text-center
                       sm:px-10
                       sm:py-12"
            >

                {{-- Icon --}}
                <div
                    class="mx-auto
                           grid h-[70px] w-[70px]
                           place-items-center
                           rounded-full
                           bg-[#E8F7EF]
                           text-[28px]
                           text-[#12B76A]"
                >

                    <i class="fa-solid fa-check"></i>

                </div>


                <div
                    class="mp-eyebrow
                           mt-6
                           justify-center"
                >
                    Message received
                </div>


                <h1
                    class="font-['Bricolage_Grotesque']
                           text-[28px]
                           font-extrabold
                           leading-[1.2]
                           text-[#0D120F]
                           sm:text-[34px]"
                >
                    Thank you for reaching out.
                </h1>


                <p
                    class="mx-auto
                           mt-3
                           max-w-[470px]
                           text-[14px]
                           leading-[1.7]
                           text-[#5A6660]"
                >
                    Your message has been sent successfully.
                    Our team will review it and get back to you
                    as soon as possible.
                </p>


                @if (!empty($reference))

                    <div
                        class="mx-auto
                               mt-6
                               max-w-[320px]
                               rounded-[12px]
                               border border-[#E4EAE6]
                               bg-[#F6F9F7]
                               px-4 py-3"
                    >

                        <div
                            class="text-[11px]
                                   uppercase
                                   tracking-[.08em]
                                   text-[#82908A]"
                        >
                            Reference
                        </div>

                        <strong
                            class="mt-1 block
                                   text-[14px]
                                   text-[#0B3D2E]"
                        >
                            {{ $reference }}
                        </strong>

                    </div>

                @endif


                <div
                    class="mt-7
                           flex flex-col
                           justify-center
                           gap-3
                           sm:flex-row"
                >

                    <a
                        href="{{ route('home') }}"
                        class="mp-btn
                               mp-btn-primary"
                    >
                        Back to homepage
                    </a>


                    <a
                        href="{{ route('support') }}"
                        class="mp-btn
                               mp-btn-outline"
                    >
                        Visit Support Centre
                    </a>

                </div>

            </div>

        </div>

    </section>

</div>

@endsection