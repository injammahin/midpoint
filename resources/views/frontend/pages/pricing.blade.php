@extends('frontend.layouts.app')


@section(
    'title',
    'Pricing | MidPoint'
)


@section(
    'meta_description',
    'Transparent MidPoint pricing for buyers and sellers.'
)


@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Formatting Helpers
    |--------------------------------------------------------------------------
    */

    $percentage = function ($value) {

        return rtrim(
            rtrim(
                number_format(
                    (float) $value,
                    2
                ),
                '0'
            ),
            '.'
        );
    };


    $money = function ($value) use ($pricing) {

        return $pricing->currency_symbol
            . number_format(
                (float) $value,
                0
            );
    };

@endphp


<div class="mp-page">

    <section class="mp-section">

        <div
            class="mp-wrap
                   !max-w-[820px]"
        >

            {{-- =========================================================
                HEADING
            ========================================================== --}}
            <div
                class="mx-auto
                       mb-[44px]
                       max-w-[650px]
                       text-center"
            >

                <div
                    class="mp-eyebrow
                           justify-center"
                >
                    {{ $pricing->page_eyebrow }}
                </div>


                <h1
                    class="font-['Bricolage_Grotesque']
                           text-[30px]
                           font-extrabold
                           leading-[1.18]
                           sm:text-[38px]"
                >
                    {{ $pricing->page_title }}
                </h1>


                <p
                    class="mx-auto
                           mt-3
                           max-w-[590px]
                           text-[15px]
                           leading-[1.65]
                           text-[#5A6660]"
                >
                    {{ $pricing->page_subtitle }}
                </p>

            </div>


            {{-- =========================================================
                PRICING CARDS
            ========================================================== --}}
            <div
                class="grid
                       grid-cols-1
                       gap-5
                       md:grid-cols-2"
            >

                {{-- =====================================================
                    SELLER
                ====================================================== --}}
                <article
                    class="rounded-[18px]
                           border-[1.5px]
                           border-[#12B76A]
                           bg-white
                           p-[30px]
                           shadow-[0_15px_40px_-28px_rgba(11,61,46,.25)]
                           sm:p-[34px]"
                >

                    {{-- Badge --}}
                    <div>

                        <span
                            class="inline-flex
                                   rounded-full
                                   bg-[#E8F7EF]
                                   px-3 py-[6px]
                                   text-[11px]
                                   font-semibold
                                   text-[#0E7A4C]"
                        >
                            {{ $pricing->seller_badge }}
                        </span>

                    </div>


                    {{-- Main Fee --}}
                    <div
                        class="mt-7
                               flex items-end
                               gap-[7px]"
                    >

                        <strong
                            class="font-['Bricolage_Grotesque']
                                   text-[48px]
                                   font-extrabold
                                   leading-none"
                        >
                            {{
                                $percentage(
                                    $pricing
                                        ->seller_service_fee_percent
                                )
                            }}%
                        </strong>


                        @if (
                            (float)
                            $pricing->seller_vat_percent
                            > 0
                        )

                            <span
                                class="mb-[5px]
                                       text-[16px]
                                       font-bold
                                       text-[#4F5E57]"
                            >
                                + VAT
                            </span>

                        @endif

                    </div>


                    {{-- Description --}}
                    <p
                        class="mt-5
                               text-[13px]
                               leading-[1.7]
                               text-[#5A6660]"
                    >
                        {{ $pricing->seller_description }}
                    </p>


                    <div
                        class="my-5
                               border-t
                               border-[#E4EAE6]"
                    ></div>


                    {{-- Product --}}
                    <div class="pricing-row">

                        <span>
                            {{ $pricing->product_price_label }}
                        </span>

                        <strong>
                            {{
                                $money(
                                    $calculation[
                                        'product_price'
                                    ]
                                )
                            }}
                        </strong>

                    </div>


                    {{-- Seller fee --}}
                    <div class="pricing-row">

                        <span>

                            {{ $pricing->seller_fee_label }}

                            ({{
                                $percentage(
                                    $pricing
                                        ->seller_service_fee_percent
                                )
                            }}%)

                        </span>


                        <strong class="text-[#F04438]">

                            − {{
                                $money(
                                    $calculation[
                                        'seller_fee'
                                    ]
                                )
                            }}

                        </strong>

                    </div>


                    {{-- VAT --}}
                    @if (
                        (float)
                        $pricing->seller_vat_percent
                        > 0
                    )

                        <div class="pricing-row">

                            <span>

                                {{ $pricing->vat_label }}

                                ({{
                                    $percentage(
                                        $pricing
                                            ->seller_vat_percent
                                    )
                                }}%
                                on service fee)

                            </span>


                            <strong class="text-[#F04438]">

                                − {{
                                    $money(
                                        $calculation[
                                            'vat'
                                        ]
                                    )
                                }}

                            </strong>

                        </div>

                    @endif


                    <div
                        class="mt-1
                               border-t
                               border-[#E4EAE6]"
                    ></div>


                    {{-- Total Charges --}}
                    <div class="pricing-row">

                        <strong>
                            {{ $pricing->total_charges_label }}
                        </strong>


                        <strong class="text-[#F04438]">

                            − {{
                                $money(
                                    $calculation[
                                        'seller_total_charges'
                                    ]
                                )
                            }}

                        </strong>

                    </div>


                    {{-- Receives --}}
                    <div
                        class="mt-5
                               flex items-center
                               justify-between
                               gap-5
                               rounded-[13px]
                               bg-[#0B3D2E]
                               px-[18px]
                               py-[17px]
                               text-white"
                    >

                        <strong class="text-[13px]">
                            {{ $pricing->seller_receive_label }}
                        </strong>


                        <strong
                            class="font-['Bricolage_Grotesque']
                                   text-[21px]"
                        >
                            {{
                                $money(
                                    $calculation[
                                        'seller_receives'
                                    ]
                                )
                            }}
                        </strong>

                    </div>

                </article>



                {{-- =====================================================
                    BUYER
                ====================================================== --}}
                <article
                    class="rounded-[18px]
                           border border-[#E4EAE6]
                           bg-white
                           p-[30px]
                           shadow-[0_15px_40px_-28px_rgba(11,61,46,.25)]
                           sm:p-[34px]"
                >

                    {{-- Badge --}}
                    <div>

                        <span
                            class="inline-flex
                                   rounded-full
                                   bg-[#F1EDFE]
                                   px-3 py-[6px]
                                   text-[11px]
                                   font-semibold
                                   text-[#7A5AF8]"
                        >
                            {{ $pricing->buyer_badge }}
                        </span>

                    </div>


                    {{-- Buyer Fee --}}
                    <div
                        class="mt-7
                               font-['Bricolage_Grotesque']
                               text-[48px]
                               font-extrabold
                               leading-none"
                    >

                        @if (
                            (float)
                            $pricing
                                ->buyer_service_fee_percent
                            === 0.0
                        )

                            {{ $pricing->currency_symbol }}0

                        @else

                            {{
                                $percentage(
                                    $pricing
                                        ->buyer_service_fee_percent
                                )
                            }}%

                        @endif

                    </div>


                    <p
                        class="mt-5
                               text-[13px]
                               leading-[1.7]
                               text-[#5A6660]"
                    >
                        {{ $pricing->buyer_description }}
                    </p>


                    <div
                        class="my-5
                               border-t
                               border-[#E4EAE6]"
                    ></div>


                    {{-- Product --}}
                    <div class="pricing-row">

                        <span>
                            {{ $pricing->product_price_label }}
                        </span>


                        <strong>
                            {{
                                $money(
                                    $calculation[
                                        'product_price'
                                    ]
                                )
                            }}
                        </strong>

                    </div>


                    {{-- Buyer Fee if > 0 --}}
                    @if (
                        (float)
                        $pricing
                            ->buyer_service_fee_percent
                        > 0
                    )

                        <div class="pricing-row">

                            <span>

                                {{ $pricing->buyer_fee_label }}

                                ({{
                                    $percentage(
                                        $pricing
                                            ->buyer_service_fee_percent
                                    )
                                }}%)

                            </span>


                            <strong>

                                {{
                                    $money(
                                        $calculation[
                                            'buyer_fee'
                                        ]
                                    )
                                }}

                            </strong>

                        </div>

                    @endif


                    {{-- Delivery --}}
                    <div class="pricing-row">

                        <span class="max-w-[210px]">
                            {{ $pricing->delivery_label }}
                        </span>


                        <strong
                            class="max-w-[100px]
                                   text-right
                                   text-[#5A6660]"
                        >
                            {{ $pricing->delivery_value }}
                        </strong>

                    </div>


                    {{-- Buyer Total --}}
                    <div
                        class="mt-6
                               flex items-center
                               justify-between
                               gap-5
                               rounded-[13px]
                               bg-[#7657F6]
                               px-[18px]
                               py-[17px]
                               text-white"
                    >

                        <strong class="text-[13px]">
                            {{ $pricing->buyer_total_label }}
                        </strong>


                        <strong
                            class="font-['Bricolage_Grotesque']
                                   text-[21px]"
                        >

                            {{
                                $money(
                                    $calculation[
                                        'buyer_total'
                                    ]
                                )
                            }}

                        </strong>

                    </div>

                </article>

            </div>


            {{-- =========================================================
                PROTECTION
            ========================================================== --}}
            @if ($pricing->protection_note)

                <p
                    class="mx-auto
                           mt-6
                           max-w-[680px]
                           text-center
                           text-[12px]
                           leading-[1.6]
                           text-[#5A6660]"
                >
                    {{ $pricing->protection_note }}
                </p>

            @endif


            {{-- =========================================================
                REFUND NOTICE
            ========================================================== --}}
            @if (
                $pricing->refund_notice_enabled
                &&
                (
                    $pricing->refund_notice_title
                    ||
                    $pricing->refund_notice_text
                )
            )

                <div
                    class="mt-5
                           flex items-start
                           gap-3
                           rounded-[14px]
                           border border-[#F79009]
                           bg-[#FEF4E6]
                           px-5 py-4"
                >

                    <div
                        class="mt-[1px]
                               text-[17px]"
                    >
                        ⚠️
                    </div>


                    <div
                        class="text-[12px]
                               leading-[1.65]
                               text-[#B54708]"
                    >

                        @if (
                            $pricing
                                ->refund_notice_title
                        )

                            <strong>
                                {{
                                    $pricing
                                        ->refund_notice_title
                                }}
                            </strong>

                        @endif


                        @if (
                            $pricing
                                ->refund_notice_text
                        )

                            <span>
                                {{
                                    $pricing
                                        ->refund_notice_text
                                }}
                            </span>

                        @endif

                    </div>

                </div>

            @endif

        </div>

    </section>

</div>

@endsection