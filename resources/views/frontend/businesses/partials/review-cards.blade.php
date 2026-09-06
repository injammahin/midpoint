@foreach ($reviews as $review)

    @php

        $reviewText =
            trim(
                (string)
                $review->review
            );


        $reviewPreviewLimit =
            420;


        $reviewIsLong =
            \Illuminate\Support\Str::length(
                $reviewText
            )
            >
            $reviewPreviewLimit;


        $reviewPreview =
            $reviewIsLong

                ? \Illuminate\Support\Str::limit(
                    $reviewText,
                    $reviewPreviewLimit,
                    ''
                )

                : $reviewText;

    @endphp


    <article
        class="shop-review"
        data-review-id="{{ $review->id }}"
    >

        <div class="shop-review-top">

            <div>

                <strong>
                    {{ optional($review->buyer)->name ?: 'Midpoint Buyer' }}
                </strong>


                @if ($review->product)

                    <span>
                        Purchased {{ $review->product->name }}
                    </span>

                @else

                    <span>
                        Verified Midpoint transaction
                    </span>

                @endif

            </div>


            <div
                class="shop-stars"
                aria-label="{{ $review->rating }} out of 5 stars"
            >

                @for(
                    $i = 1;
                    $i <= 5;
                    $i++
                )

                    <i
                        class="fa-solid fa-star {{ $i <= $review->rating ? 'active' : '' }}"
                        aria-hidden="true"
                    ></i>

                @endfor

            </div>

        </div>



        <div class="shop-review-copy">

            <p>

                <span
                    data-review-preview
                    class="shop-review-text"
                >{{ $reviewPreview }}</span>


                @if ($reviewIsLong)

                    <span
                        data-review-full
                        class="shop-review-text"
                        hidden
                    >{{ $reviewText }}</span>

                @endif

            </p>


            @if ($reviewIsLong)

                <button
                    type="button"
                    class="shop-review-toggle"
                    data-review-toggle
                    aria-expanded="false"
                >
                    See more
                </button>

            @endif

        </div>



        <small>
            {{ $review->created_at->format('d M Y') }}
        </small>

    </article>

@endforeach
