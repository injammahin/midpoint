@extends('buyer.layouts.app')


@section('title', 'Open a Dispute')


@section('content')

<div class="dp-page">

    <div class="dp-head">

        <a
            href="{{
                route(
                    'buyer.transactions.show',
                    [
                        'secureTransaction' =>
                            $transaction->public_token,
                    ]
                )
            }}"
            class="dp-back"
        >
            ← Back to transaction
        </a>


        <h1>
            Open a dispute
        </h1>


        <p>
            Dispute for
            <strong>{{ $transaction->title }}</strong>
            ·
            {{ $transaction->reference }}
        </p>

    </div>


    @if($errors->any())

        <div class="dp-errors">

            <strong>
                Please correct the following:
            </strong>

            <ul>

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="dp-layout">

        <form
            method="POST"
            action="{{
                route(
                    'buyer.transactions.dispute.store',
                    [
                        'secureTransaction' =>
                            $transaction->public_token,
                    ]
                )
            }}"
            enctype="multipart/form-data"
            class="dp-main"
        >

            @csrf


            <div class="dp-warning">

                <i class="fa-solid fa-pause"></i>

                <div>

                    <strong>
                        Opening a dispute pauses automatic payment release.
                    </strong>

                    <span>
                        Seller payout remains pending while Midpoint reviews the case.
                    </span>

                </div>

            </div>


            <div class="dp-card">

                <h2>
                    What went wrong?
                </h2>


                <label>
                    Dispute reason
                </label>


                <select
                    name="reason"
                    required
                >

                    <option value="">
                        Select the problem
                    </option>

                    <option
                        value="not_received"
                        @selected(old('reason') === 'not_received')
                    >
                        Item not received
                    </option>

                    <option
                        value="not_as_described"
                        @selected(old('reason') === 'not_as_described')
                    >
                        Item not as described
                    </option>

                    <option
                        value="damaged"
                        @selected(old('reason') === 'damaged')
                    >
                        Item arrived damaged
                    </option>

                    <option
                        value="wrong_item"
                        @selected(old('reason') === 'wrong_item')
                    >
                        Wrong item received
                    </option>

                    <option
                        value="missing_parts"
                        @selected(old('reason') === 'missing_parts')
                    >
                        Missing parts or accessories
                    </option>

                    <option
                        value="other"
                        @selected(old('reason') === 'other')
                    >
                        Other problem
                    </option>

                </select>


                <label>
                    Describe the issue in detail
                </label>


                <textarea
                    name="description"
                    rows="7"
                    required
                    placeholder="Explain what was promised, what you received, defects, missing items, model differences or any other important details."
                >{{ old('description') }}</textarea>

            </div>


            <div class="dp-card">

                <h2>
                    Upload evidence
                </h2>


                <p>
                    Upload at least 2 files. You can use photos,
                    screenshots, PDF documents or short videos.
                </p>


                <label
                    for="evidence"
                    class="dp-upload"
                >

                    <i class="fa-solid fa-cloud-arrow-up"></i>

                    <strong>
                        Choose evidence files
                    </strong>

                    <span>
                        JPG, PNG, WEBP, PDF, MP4 or MOV
                    </span>

                    <input
                        id="evidence"
                        type="file"
                        name="evidence[]"
                        multiple
                        required
                        accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4,.mov"
                    >

                </label>


                <div
                    id="evidenceList"
                    class="dp-files"
                ></div>

            </div>


            <div class="dp-card">

                <h2>
                    What outcome do you want?
                </h2>


                <label class="dp-radio">

                    <input
                        type="radio"
                        name="desired_outcome"
                        value="full_refund"
                        required
                        @checked(old('desired_outcome') === 'full_refund')
                    >

                    <span>

                        <strong>
                            Full refund
                        </strong>

                        <small>
                            Request to return the item and receive a full eligible refund.
                        </small>

                    </span>

                </label>


                <label class="dp-radio">

                    <input
                        type="radio"
                        name="desired_outcome"
                        value="partial_refund"
                        @checked(old('desired_outcome') === 'partial_refund')
                    >

                    <span>

                        <strong>
                            Partial refund
                        </strong>

                        <small>
                            Keep the item but request a reduced final price.
                        </small>

                    </span>

                </label>


                <label class="dp-radio">

                    <input
                        type="radio"
                        name="desired_outcome"
                        value="replacement"
                        @checked(old('desired_outcome') === 'replacement')
                    >

                    <span>

                        <strong>
                            Replacement
                        </strong>

                        <small>
                            Ask the seller to replace the item.
                        </small>

                    </span>

                </label>

            </div>


            <div class="dp-card">

                <h2>
                    Return information
                </h2>


                <label>
                    How will you return the item?
                </label>


                <input
                    type="text"
                    name="return_method"
                    value="{{ old('return_method') }}"
                    placeholder="e.g. ABC Transport, tracking number or courier details"
                >


                <label>
                    Return receipt / proof
                </label>


                <input
                    type="file"
                    name="return_proof"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                >

            </div>


            <button
                type="submit"
                class="dp-submit"
            >

                <i class="fa-solid fa-scale-balanced"></i>

                Submit dispute

            </button>

        </form>



        <aside class="dp-side">

            <div class="dp-card">

                <h2>
                    How it works
                </h2>


                <div class="dp-step">

                    <span>1</span>

                    <div>

                        <strong>
                            You submit evidence
                        </strong>

                        <p>
                            Midpoint receives your explanation and supporting files.
                        </p>

                    </div>

                </div>


                <div class="dp-step">

                    <span>2</span>

                    <div>

                        <strong>
                            Seller is notified
                        </strong>

                        <p>
                            The seller receives an email and Midpoint notification.
                        </p>

                    </div>

                </div>


                <div class="dp-step">

                    <span>3</span>

                    <div>

                        <strong>
                            Automatic release pauses
                        </strong>

                        <p>
                            The transaction will not auto-release while the dispute is open.
                        </p>

                    </div>

                </div>


                <div class="dp-step">

                    <span>4</span>

                    <div>

                        <strong>
                            Midpoint reviews
                        </strong>

                        <p>
                            The administrator can review both sides and resolve the case.
                        </p>

                    </div>

                </div>

            </div>


            <div class="dp-card dp-safe">

                <i class="fa-solid fa-shield-halved"></i>

                <div>

                    <strong>
                        Payment remains protected
                    </strong>

                    <p>
                        Seller payout is paused while the dispute remains open.
                    </p>

                </div>

            </div>

        </aside>

    </div>

</div>



<style>

.dp-page {
    width: 100%;
    max-width: 1050px;
    margin: 0 auto;
}

.dp-head {
    margin-bottom: 20px;
}

.dp-back {
    color: #12B76A;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.dp-head h1 {
    margin: 8px 0 4px;
    color: #101915;
    font-family: 'Bricolage Grotesque', sans-serif;
    font-size: 28px;
}

.dp-head p {
    margin: 0;
    color: #6B7871;
    font-size: 12px;
}

.dp-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 315px;
    gap: 18px;
    align-items: start;
}

.dp-main {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.dp-card {
    padding: 21px;
    border: 1px solid #DCE5E0;
    border-radius: 16px;
    background: #FFFFFF;
}

.dp-card h2 {
    margin: 0 0 16px;
    color: #17251F;
    font-size: 15px;
}

.dp-card > p {
    margin: -7px 0 15px;
    color: #728078;
    font-size: 11px;
    line-height: 1.6;
}

.dp-card label:not(.dp-radio):not(.dp-upload) {
    display: block;
    margin: 15px 0 7px;
    color: #25332C;
    font-size: 12px;
    font-weight: 700;
}

.dp-card select,
.dp-card textarea,
.dp-card input[type="text"],
.dp-card input[type="file"] {
    width: 100%;
    border: 1px solid #DCE5E0;
    border-radius: 10px;
    background: #FFFFFF;
    color: #26352D;
    font-family: inherit;
    font-size: 12px;
    outline: none;
}

.dp-card select,
.dp-card input[type="text"] {
    height: 45px;
    padding: 0 13px;
}

.dp-card textarea {
    padding: 13px;
    resize: vertical;
}

.dp-warning {
    display: flex;
    gap: 11px;
    padding: 15px;
    border: 1px solid #FECDCA;
    border-radius: 12px;
    background: #FEF3F2;
    color: #B42318;
}

.dp-warning strong,
.dp-warning span {
    display: block;
}

.dp-warning strong {
    font-size: 12px;
}

.dp-warning span {
    margin-top: 4px;
    font-size: 11px;
    line-height: 1.5;
}

.dp-upload {
    min-height: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 1px dashed #BFCBC4;
    border-radius: 12px;
    background: #FAFCFB;
    cursor: pointer;
}

.dp-upload i {
    color: #12B76A;
    font-size: 22px;
}

.dp-upload strong {
    margin-top: 9px;
    font-size: 12px;
}

.dp-upload span {
    margin-top: 4px;
    color: #7B8781;
    font-size: 10px;
}

.dp-upload input {
    display: none;
}

.dp-files {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.dp-file {
    padding: 8px 10px;
    border: 1px solid #DCE5E0;
    border-radius: 8px;
    background: #F7F9F8;
    color: #536159;
    font-size: 10px;
}

.dp-radio {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 9px;
    padding: 14px;
    border: 1px solid #DCE5E0;
    border-radius: 11px;
    cursor: pointer;
}

.dp-radio strong,
.dp-radio small {
    display: block;
}

.dp-radio strong {
    color: #17251F;
    font-size: 12px;
}

.dp-radio small {
    margin-top: 4px;
    color: #69766F;
    font-size: 10px;
    line-height: 1.5;
}

.dp-submit {
    min-height: 48px;
    border: 0;
    border-radius: 11px;
    background: #F04438;
    color: #FFFFFF;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
}

.dp-side {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.dp-step {
    display: flex;
    gap: 10px;
    margin-top: 17px;
}

.dp-step > span {
    width: 25px;
    height: 25px;
    flex: 0 0 25px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #12B76A;
    color: #FFFFFF;
    font-size: 10px;
    font-weight: 800;
}

.dp-step strong {
    font-size: 12px;
}

.dp-step p {
    margin: 4px 0 0;
    color: #748079;
    font-size: 10px;
    line-height: 1.55;
}

.dp-safe {
    display: flex;
    gap: 10px;
    border-color: #ABEFC6;
    background: #ECFDF3;
    color: #067647;
}

.dp-safe p {
    margin: 5px 0 0;
    color: #47705D;
    font-size: 10px;
}

.dp-errors {
    margin-bottom: 15px;
    padding: 14px;
    border: 1px solid #FECDCA;
    border-radius: 10px;
    background: #FEF3F2;
    color: #B42318;
    font-size: 11px;
}

@media(max-width: 850px) {
    .dp-layout {
        grid-template-columns: 1fr;
    }
}

</style>



<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const input =
            document.getElementById(
                'evidence'
            );

        const list =
            document.getElementById(
                'evidenceList'
            );

        input?.addEventListener(
            'change',
            function () {

                list.innerHTML = '';

                Array.from(
                    input.files
                ).forEach(
                    function (file) {

                        const item =
                            document.createElement(
                                'div'
                            );

                        item.className =
                            'dp-file';

                        item.textContent =
                            file.name;

                        list.appendChild(
                            item
                        );

                    }
                );

            }
        );

    }
);

</script>

@endsection