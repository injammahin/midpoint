{{-- =========================================================
    SELLER REVIEW MODAL
========================================================== --}}

<div
    id="sellerReviewModal"
    class="seller-review-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="sellerReviewModalTitle"
>

    <div
        class="seller-review-backdrop"
        aria-hidden="true"
    ></div>


    <div class="seller-review-dialog">

        <div class="seller-review-icon">

            <i class="fa-solid fa-star"></i>

        </div>


        <div class="seller-review-heading">

            <span>
                Transaction completed
            </span>


            <h2 id="sellerReviewModalTitle">
                How was your experience with the seller?
            </h2>


            <p>
                Your review helps other Midpoint buyers make confident
                decisions. Please rate
                <strong>
                    {{ optional($transaction->seller)->name ?: 'this seller' }}
                </strong>
                and leave an honest comment.
            </p>

        </div>



        <form
            method="POST"
            action="{{
                route(
                    'buyer.transactions.review.store',
                    $transaction
                )
            }}"
            id="sellerReviewForm"
        >

            @csrf


            {{-- =====================================================
                STAR RATING
            ====================================================== --}}

            <div class="seller-review-field">

                <label>
                    Your rating
                    <span>
                        Required
                    </span>
                </label>


                <div
                    class="seller-review-stars"
                    role="radiogroup"
                    aria-label="Seller rating"
                    id="sellerReviewStars"
                >

                    @for(
                        $rating = 1;
                        $rating <= 5;
                        $rating++
                    )

                        <label
                            class="seller-review-star"
                            data-rating-value="{{ $rating }}"
                            title="{{ $rating }} {{ $rating === 1 ? 'star' : 'stars' }}"
                        >

                            <input
                                type="radio"
                                name="rating"
                                value="{{ $rating }}"
                                @checked(
                                    (int)
                                    old(
                                        'rating',
                                        0
                                    )
                                    ===
                                    $rating
                                )
                                required
                            >

                            <i
                                class="fa-solid fa-star"
                                aria-hidden="true"
                            ></i>

                            <span class="sr-only">
                                {{ $rating }}
                                {{ $rating === 1 ? 'star' : 'stars' }}
                            </span>

                        </label>

                    @endfor

                </div>


                <div
                    id="sellerReviewRatingLabel"
                    class="seller-review-rating-label"
                >
                    Select 1 to 5 stars
                </div>


                @error('rating')

                    <p class="seller-review-error">
                        {{ $message }}
                    </p>

                @enderror

            </div>



            {{-- =====================================================
                REVIEW COMMENT
            ====================================================== --}}

            <div class="seller-review-field">

                <label for="sellerReviewText">

                    Review

                    <span>
                        Required · maximum 10,000 characters
                    </span>

                </label>


                <textarea
                    id="sellerReviewText"
                    name="review"
                    maxlength="10000"
                    rows="7"
                    placeholder="Tell other buyers about the item, communication, delivery and your overall experience with this seller..."
                    required
                >{{ old('review') }}</textarea>


                <div class="seller-review-meta">

                    <span>
                        Be specific, fair and respectful.
                    </span>


                    <strong id="sellerReviewCharacterCount">
                        0 / 10,000
                    </strong>

                </div>


                @error('review')

                    <p class="seller-review-error">
                        {{ $message }}
                    </p>

                @enderror

            </div>



            {{-- =====================================================
                TRANSACTION CONTEXT
            ====================================================== --}}

            <div class="seller-review-transaction">

                <i class="fa-solid fa-shield-halved"></i>


                <div>

                    <strong>
                        Verified Midpoint purchase
                    </strong>


                    <span>
                        {{ $transaction->title }}
                        ·
                        {{ $transaction->reference }}
                    </span>

                </div>

            </div>



            {{-- =====================================================
                SUBMIT
            ====================================================== --}}

            <button
                type="submit"
                class="seller-review-submit"
                id="sellerReviewSubmit"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Submit seller review

            </button>


            <p class="seller-review-required-note">

                <i class="fa-solid fa-circle-info"></i>

                A rating and review are required for this completed
                transaction. You can submit one review for this order.

            </p>

        </form>

    </div>

</div>



@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Seller Review Modal
    |--------------------------------------------------------------------------
    */

    body.seller-review-open {

        overflow:
            hidden;

    }


    .seller-review-modal {

        position:
            fixed;

        inset:
            0;

        z-index:
            200000;

        display:
            grid;

        place-items:
            center;

        padding:
            20px;

    }


    .seller-review-backdrop {

        position:
            absolute;

        inset:
            0;

        background:
            rgba(
                4,
                22,
                16,
                .76
            );

        backdrop-filter:
            blur(5px);

    }


    .seller-review-dialog {

        position:
            relative;

        z-index:
            2;

        width:
            min(
                100%,
                560px
            );

        max-height:
            calc(
                100vh
                -
                40px
            );

        overflow-y:
            auto;

        padding:
            28px;

        border:
            1px solid
            #DDE6E1;

        border-radius:
            20px;

        background:
            #FFFFFF;

        box-shadow:

            0 30px 90px
            rgba(
                0,
                0,
                0,
                .30
            );

    }


    .seller-review-icon {

        width:
            52px;

        height:
            52px;

        display:
            grid;

        place-items:
            center;

        margin:
            0 auto 14px;

        border-radius:
            15px;

        color:
            #F4B400;

        background:
            #FFF8DF;

        font-size:
            22px;

    }


    .seller-review-heading {

        text-align:
            center;

    }


    .seller-review-heading > span {

        display:
            inline-flex;

        padding:
            5px 9px;

        border-radius:
            999px;

        color:
            #087443;

        background:
            #E8F7EF;

        font-size:
            10px;

        font-weight:
            800;

        text-transform:
            uppercase;

        letter-spacing:
            .08em;

    }


    .seller-review-heading h2 {

        margin:
            10px 0 6px;

        color:
            #101915;

        font-family:
            'Bricolage Grotesque',
            sans-serif;

        font-size:
            22px;

        font-weight:
            800;

        line-height:
            1.25;

    }


    .seller-review-heading p {

        max-width:
            460px;

        margin:
            0 auto;

        color:
            #69766F;

        font-size:
            12px;

        line-height:
            1.65;

    }


    .seller-review-heading p strong {

        color:
            #26342D;

    }


    #sellerReviewForm {

        margin-top:
            22px;

    }


    .seller-review-field {

        margin-bottom:
            18px;

    }


    .seller-review-field > label {

        display:
            flex;

        align-items:
            center;

        justify-content:
            space-between;

        gap:
            10px;

        margin-bottom:
            8px;

        color:
            #26342D;

        font-size:
            12px;

        font-weight:
            800;

    }


    .seller-review-field > label span {

        color:
            #87938D;

        font-size:
            9px;

        font-weight:
            500;

    }


    .seller-review-stars {

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        gap:
            9px;

        padding:
            14px;

        border:
            1px solid
            #E1E8E4;

        border-radius:
            13px;

        background:
            #FAFCFB;

    }


    .seller-review-star {

        position:
            relative;

        display:
            grid;

        place-items:
            center;

        cursor:
            pointer;

    }


    .seller-review-star input {

        position:
            absolute;

        width:
            1px;

        height:
            1px;

        opacity:
            0;

        pointer-events:
            none;

    }


    .seller-review-star i {

        color:
            #D6DDD9;

        font-size:
            30px;

        transition:

            color
            .15s ease,

            transform
            .15s ease;

    }


    .seller-review-star:hover i {

        transform:
            scale(1.08);

    }


    .seller-review-star.is-active i {

        color:
            #F4B400;

    }


    .seller-review-rating-label {

        margin-top:
            7px;

        color:
            #7A8780;

        font-size:
            10px;

        text-align:
            center;

    }


    .seller-review-field textarea {

        width:
            100%;

        min-height:
            145px;

        padding:
            12px 13px;

        resize:
            vertical;

        border:
            1px solid
            #DCE5E0;

        border-radius:
            12px;

        outline:
            none;

        background:
            #FFFFFF;

        color:
            #17251F;

        font-family:
            inherit;

        font-size:
            12px;

        line-height:
            1.65;

    }


    .seller-review-field textarea:focus {

        border-color:
            #12B76A;

        box-shadow:

            0 0 0 3px
            rgba(
                18,
                183,
                106,
                .09
            );

    }


    .seller-review-meta {

        display:
            flex;

        justify-content:
            space-between;

        gap:
            12px;

        margin-top:
            7px;

        color:
            #87938D;

        font-size:
            9px;

    }


    .seller-review-meta strong {

        flex:
            0 0 auto;

        color:
            #526059;

    }


    .seller-review-meta strong.is-near-limit {

        color:
            #B54708;

    }


    .seller-review-meta strong.is-at-limit {

        color:
            #B42318;

    }


    .seller-review-error {

        margin:
            6px 0 0;

        color:
            #B42318;

        font-size:
            10px;

        font-weight:
            700;

    }


    .seller-review-transaction {

        display:
            flex;

        align-items:
            flex-start;

        gap:
            9px;

        margin-bottom:
            18px;

        padding:
            11px;

        border:
            1px solid
            #D8EDE2;

        border-radius:
            11px;

        color:
            #087443;

        background:
            #F3FBF7;

    }


    .seller-review-transaction > i {

        margin-top:
            2px;

    }


    .seller-review-transaction strong,
    .seller-review-transaction span {

        display:
            block;

    }


    .seller-review-transaction strong {

        color:
            #075F3B;

        font-size:
            11px;

    }


    .seller-review-transaction span {

        margin-top:
            2px;

        color:
            #5F756A;

        font-size:
            9px;

    }


    .seller-review-submit {

        width:
            100%;

        min-height:
            46px;

        display:
            inline-flex;

        align-items:
            center;

        justify-content:
            center;

        gap:
            7px;

        border:
            0;

        border-radius:
            12px;

        color:
            #FFFFFF;

        background:
            #12B76A;

        font-size:
            12px;

        font-weight:
            800;

        cursor:
            pointer;

        transition:

            transform
            .15s ease,

            filter
            .15s ease;

    }


    .seller-review-submit:hover {

        transform:
            translateY(-1px);

        filter:
            brightness(1.03);

    }


    .seller-review-submit:disabled {

        cursor:
            wait;

        opacity:
            .72;

    }


    .seller-review-required-note {

        display:
            flex;

        justify-content:
            center;

        align-items:
            flex-start;

        gap:
            6px;

        margin:
            10px 0 0;

        color:
            #87938D;

        font-size:
            9px;

        line-height:
            1.5;

        text-align:
            center;

    }


    .seller-review-required-note i {

        margin-top:
            2px;

        color:
            #12B76A;

    }


    @media(max-width: 600px) {

        .seller-review-modal {

            padding:
                10px;

        }


        .seller-review-dialog {

            max-height:
                calc(
                    100vh
                    -
                    20px
                );

            padding:
                21px;

            border-radius:
                16px;

        }


        .seller-review-star i {

            font-size:
                25px;

        }


        .seller-review-stars {

            gap:
                6px;

        }


        .seller-review-meta {

            align-items:
                flex-start;

            flex-direction:
                column;

            gap:
                4px;

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
        | Lock Background Scroll
        |--------------------------------------------------------------------------
        */

        document.body.classList.add(
            'seller-review-open'
        );


        /*
        |--------------------------------------------------------------------------
        | Star Rating
        |--------------------------------------------------------------------------
        */

        const starContainer =
            document.getElementById(
                'sellerReviewStars'
            );


        const ratingLabel =
            document.getElementById(
                'sellerReviewRatingLabel'
            );


        const stars =
            Array.from(
                document.querySelectorAll(
                    '.seller-review-star'
                )
            );


        const ratingLabels = {

            1:
                '1 star · Poor',

            2:
                '2 stars · Fair',

            3:
                '3 stars · Good',

            4:
                '4 stars · Very good',

            5:
                '5 stars · Excellent',

        };


        function selectedRating()
        {
            return parseInt(
                starContainer
                    ?.querySelector(
                        'input[name="rating"]:checked'
                    )
                    ?.value
                ||
                0,
                10
            );
        }


        function paintStars(
            rating
        ) {

            stars.forEach(
                function (star) {

                    const value =
                        parseInt(
                            star.dataset.ratingValue
                            ||
                            0,
                            10
                        );


                    star.classList.toggle(
                        'is-active',
                        value <= rating
                    );
                }
            );


            if (
                ratingLabel
            ) {

                ratingLabel.textContent =
                    ratingLabels[
                        rating
                    ]
                    ||
                    'Select 1 to 5 stars';
            }
        }


        stars.forEach(
            function (star) {

                const input =
                    star.querySelector(
                        'input'
                    );


                input?.addEventListener(
                    'change',
                    function () {

                        paintStars(
                            selectedRating()
                        );
                    }
                );


                star.addEventListener(
                    'mouseenter',
                    function () {

                        paintStars(
                            parseInt(
                                star.dataset.ratingValue,
                                10
                            )
                        );
                    }
                );
            }
        );


        starContainer?.addEventListener(
            'mouseleave',
            function () {

                paintStars(
                    selectedRating()
                );
            }
        );


        paintStars(
            selectedRating()
        );


        /*
        |--------------------------------------------------------------------------
        | Review Character Count
        |--------------------------------------------------------------------------
        */

        const textarea =
            document.getElementById(
                'sellerReviewText'
            );


        const counter =
            document.getElementById(
                'sellerReviewCharacterCount'
            );


        function renderCount()
        {
            if (
                !textarea
                ||
                !counter
            ) {

                return;
            }


            const length =
                Array
                    .from(
                        textarea.value
                    )
                    .length;


            counter.textContent =
                length.toLocaleString()
                +
                ' / 10,000';


            counter.classList.toggle(
                'is-near-limit',
                length >= 9000
                &&
                length < 10000
            );


            counter.classList.toggle(
                'is-at-limit',
                length >= 10000
            );
        }


        textarea?.addEventListener(
            'input',
            renderCount
        );


        renderCount();


        /*
        |--------------------------------------------------------------------------
        | Submit Protection
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'sellerReviewForm'
            );


        const submitButton =
            document.getElementById(
                'sellerReviewSubmit'
            );


        form?.addEventListener(
            'submit',
            function () {

                if (
                    submitButton
                ) {

                    submitButton.disabled =
                        true;


                    submitButton.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> Publishing review...';
                }
            }
        );

    }
);

</script>

@endpush
