@extends('admin.layouts.app')


@section('title', 'FAQ Management')

@section('page-title', 'FAQ Management')


@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="admin-faq-page-head">

        <div>

            <h2>
                FAQ Management
            </h2>

            <p>
                Add and maintain questions displayed on the public website.
            </p>

        </div>


        <button
            type="button"
            class="admin-faq-add-button"
            id="openCreateFaqModal"
        >

            <i class="fa-solid fa-plus"></i>

            Add FAQ

        </button>

    </div>



    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))

        <div class="admin-success-alert">

            <i class="fa-solid fa-circle-check"></i>

            {{ session('success') }}

        </div>

    @endif



    {{-- =========================================================
        STATS
    ========================================================== --}}
    <div class="admin-faq-stat-grid">

        <div class="admin-card admin-faq-stat">

            <span>
                Total FAQs
            </span>

            <strong>
                {{ $stats['total'] }}
            </strong>

            <i class="fa-regular fa-circle-question"></i>

        </div>


        <div class="admin-card admin-faq-stat">

            <span>
                Active
            </span>

            <strong>
                {{ $stats['active'] }}
            </strong>

            <i class="fa-solid fa-circle-check"></i>

        </div>


        <div class="admin-card admin-faq-stat">

            <span>
                Inactive
            </span>

            <strong>
                {{ $stats['inactive'] }}
            </strong>

            <i class="fa-solid fa-circle-pause"></i>

        </div>


        <div class="admin-card admin-faq-stat">

            <span>
                Homepage
            </span>

            <strong>
                {{ $stats['homepage'] }}
            </strong>

            <i class="fa-solid fa-house"></i>

        </div>

    </div>



    {{-- =========================================================
        FILTERS
    ========================================================== --}}
    <div class="admin-card admin-faq-toolbar">

        <form
            method="GET"
            action="{{ route('admin.website-settings.faqs') }}"
            class="admin-faq-filter-form"
        >

            <div class="admin-faq-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search FAQ..."
                >

            </div>


            <select name="status">

                <option value="">
                    All statuses
                </option>

                <option
                    value="active"
                    {{ request('status') === 'active' ? 'selected' : '' }}
                >
                    Active
                </option>

                <option
                    value="inactive"
                    {{ request('status') === 'inactive' ? 'selected' : '' }}
                >
                    Inactive
                </option>

            </select>


            <select name="home">

                <option value="">
                    All FAQs
                </option>

                <option
                    value="yes"
                    {{ request('home') === 'yes' ? 'selected' : '' }}
                >
                    Homepage FAQs
                </option>

            </select>


            <button
                type="submit"
                class="admin-faq-filter-button"
            >

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            @if(
                request()->filled('search')
                ||
                request()->filled('status')
                ||
                request()->filled('home')
            )

                <a
                    href="{{ route('admin.website-settings.faqs') }}"
                    class="admin-faq-reset"
                >
                    Reset
                </a>

            @endif

        </form>

    </div>



    {{-- =========================================================
        FAQ TABLE
    ========================================================== --}}
    <div class="admin-card admin-faq-table-card">

        <div class="admin-faq-table-wrapper">

            <table class="admin-faq-table">

                <thead>

                    <tr>

                        <th>
                            Order
                        </th>

                        <th>
                            Question
                        </th>

                        <th>
                            Homepage
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Updated
                        </th>

                        <th class="text-right">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($faqs as $faq)

                        <tr>

                            {{-- =========================================
                                ORDER
                            ========================================== --}}
                            <td>

                                <span class="admin-faq-order">
                                    {{ $faq->sort_order }}
                                </span>

                            </td>


                            {{-- =========================================
                                QUESTION
                            ========================================== --}}
                            <td>

                                <div class="admin-faq-question-cell">

                                    <strong>
                                        {{ $faq->question }}
                                    </strong>


                                    <span>

                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $faq->answer,
                                                100
                                            )
                                        }}

                                    </span>

                                </div>

                            </td>


                            {{-- =========================================
                                HOMEPAGE
                            ========================================== --}}
                            <td>

                                @if($faq->show_on_home)

                                    <span class="admin-faq-badge home">

                                        <i class="fa-solid fa-house"></i>

                                        Yes

                                    </span>

                                @else

                                    <span class="admin-faq-badge neutral">
                                        No
                                    </span>

                                @endif

                            </td>


                            {{-- =========================================
                                STATUS
                            ========================================== --}}
                            <td>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.website-settings.faqs.toggle-status',
                                        $faq
                                    ) }}"
                                >

                                    @csrf
                                    @method('PATCH')


                                    <button
                                        type="submit"
                                        class="admin-faq-status {{ $faq->is_active ? 'active' : 'inactive' }}"
                                    >

                                        <span></span>

                                        {{
                                            $faq->is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}

                                    </button>

                                </form>

                            </td>


                            {{-- =========================================
                                UPDATED
                            ========================================== --}}
                            <td>

                                <span class="admin-faq-date">

                                    {{
                                        $faq
                                            ->updated_at
                                            ->diffForHumans()
                                    }}

                                </span>

                            </td>


                            {{-- =========================================
                                ACTIONS
                            ========================================== --}}
                            <td>

                                <div class="admin-faq-actions">

                                    {{-- EDIT MODAL BUTTON --}}
                                    <button
                                        type="button"
                                        class="admin-faq-edit-button"
                                        title="Edit FAQ"
                                        data-faq="{{ base64_encode(
                                            json_encode([
                                                'id' => $faq->id,
                                                'question' => $faq->question,
                                                'answer' => $faq->answer,
                                                'sort_order' => $faq->sort_order,
                                                'is_active' => (bool) $faq->is_active,
                                                'show_on_home' => (bool) $faq->show_on_home,
                                            ])
                                        ) }}"
                                    >

                                        <i class="fa-solid fa-pen"></i>

                                    </button>


                                    {{-- DELETE --}}
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.website-settings.faqs.destroy',
                                            $faq
                                        ) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this FAQ?');"
                                    >

                                        @csrf
                                        @method('DELETE')


                                        <button
                                            type="submit"
                                            class="delete"
                                            title="Delete FAQ"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="admin-faq-empty-cell"
                            >

                                <div>

                                    <i class="fa-regular fa-circle-question"></i>

                                    <strong>
                                        No FAQs found
                                    </strong>

                                    <p>
                                        Create your first FAQ to display it
                                        on the public website.
                                    </p>


                                    <button
                                        type="button"
                                        class="admin-faq-empty-add"
                                        id="openCreateFaqModalEmpty"
                                    >
                                        Add FAQ
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>



    {{-- =========================================================
        PAGINATION
    ========================================================== --}}
    @if($faqs->hasPages())

        <div class="mt-5">

            {{ $faqs->links() }}

        </div>

    @endif



    {{-- =========================================================
        FAQ CREATE / EDIT MODAL
    ========================================================== --}}
    <div
        id="faqModal"
        class="admin-faq-modal"
        aria-hidden="true"
    >

        {{-- Overlay --}}
        <div
            class="admin-faq-modal-overlay"
            data-close-faq-modal
        ></div>


        {{-- Modal --}}
        <div
            class="admin-faq-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="faqModalTitle"
        >

            {{-- =============================================
                MODAL HEADER
            ============================================== --}}
            <div class="admin-faq-modal-header">

                <div>

                    <span class="admin-faq-modal-icon">

                        <i
                            id="faqModalHeaderIcon"
                            class="fa-solid fa-plus"
                        ></i>

                    </span>


                    <div>

                        <h3 id="faqModalTitle">
                            Add FAQ
                        </h3>

                        <p id="faqModalSubtitle">
                            Create a new question for the public website.
                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    class="admin-faq-modal-close"
                    data-close-faq-modal
                    aria-label="Close modal"
                >

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>



            {{-- =============================================
                FORM
            ============================================== --}}
            <form
                method="POST"
                action="{{ route('admin.website-settings.faqs.store') }}"
                id="faqModalForm"
                data-store-url="{{ route('admin.website-settings.faqs.store') }}"
                data-update-url="{{ url('/admin/website-settings/faqs/__FAQ__') }}"
            >

                @csrf


                {{-- Method used only for Edit --}}
                <input
                    type="hidden"
                    name="_method"
                    id="faqFormMethod"
                    value=""
                    disabled
                >


                {{-- Used to restore modal after validation error --}}
                <input
                    type="hidden"
                    name="form_mode"
                    id="faqFormMode"
                    value="{{ old('form_mode', 'create') }}"
                >


                <input
                    type="hidden"
                    name="faq_id"
                    id="faqFormId"
                    value="{{ old('faq_id') }}"
                >



                {{-- =============================================
                    MODAL BODY
                ============================================== --}}
                <div class="admin-faq-modal-body">


                    {{-- =========================================
                        VALIDATION ERRORS
                    ========================================== --}}
                    @if($errors->any())

                        <div class="admin-faq-error">

                            <i class="fa-solid fa-circle-exclamation"></i>


                            <div>

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

                        </div>

                    @endif



                    {{-- =========================================
                        QUESTION
                    ========================================== --}}
                    <div class="admin-faq-field">

                        <label for="faqQuestion">

                            Question

                            <span>
                                *
                            </span>

                        </label>


                        <input
                            id="faqQuestion"
                            type="text"
                            name="question"
                            value="{{ old('question') }}"
                            placeholder="e.g. Who pays MidPoint's fee?"
                            maxlength="500"
                            required
                        >


                        <small>
                            This is the question visitors will see.
                        </small>

                    </div>



                    {{-- =========================================
                        ANSWER
                    ========================================== --}}
                    <div class="admin-faq-field">

                        <label for="faqAnswer">

                            Answer

                            <span>
                                *
                            </span>

                        </label>


                        <textarea
                            id="faqAnswer"
                            name="answer"
                            rows="7"
                            placeholder="Enter the complete answer..."
                            maxlength="10000"
                            required
                        >{{ old('answer') }}</textarea>


                        <div class="admin-faq-field-bottom">

                            <small>
                                Write a clear answer using plain text.
                            </small>


                            <small id="faqAnswerCount">
                                0 / 10000
                            </small>

                        </div>

                    </div>



                    {{-- =========================================
                        SETTINGS
                    ========================================== --}}
                    <div class="admin-faq-modal-settings">

                        {{-- ORDER --}}
                        <div class="admin-faq-field">

                            <label for="faqSortOrder">
                                Display Order
                            </label>


                            <input
                                id="faqSortOrder"
                                type="number"
                                name="sort_order"
                                value="{{ old('sort_order', $nextSortOrder) }}"
                                min="0"
                                max="9999"
                                required
                            >


                            <small>
                                Lower numbers appear first.
                            </small>

                        </div>


                        {{-- OPTIONS --}}
                        <div class="admin-faq-options">

                            {{-- Active --}}
                            <label class="admin-faq-option">

                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >


                                <input
                                    type="checkbox"
                                    name="is_active"
                                    id="faqIsActive"
                                    value="1"
                                    {{ old('is_active', 1) ? 'checked' : '' }}
                                >


                                <span>

                                    <strong>
                                        Active
                                    </strong>

                                    <small>
                                        Show this FAQ publicly.
                                    </small>

                                </span>

                            </label>


                            {{-- Homepage --}}
                            <label class="admin-faq-option">

                                <input
                                    type="hidden"
                                    name="show_on_home"
                                    value="0"
                                >


                                <input
                                    type="checkbox"
                                    name="show_on_home"
                                    id="faqShowOnHome"
                                    value="1"
                                    {{ old('show_on_home', 0) ? 'checked' : '' }}
                                >


                                <span>

                                    <strong>
                                        Show on homepage
                                    </strong>

                                    <small>
                                        Include in the homepage FAQ section.
                                    </small>

                                </span>

                            </label>

                        </div>

                    </div>

                </div>



                {{-- =============================================
                    FOOTER
                ============================================== --}}
                <div class="admin-faq-modal-footer">

                    <button
                        type="button"
                        class="admin-faq-modal-cancel"
                        data-close-faq-modal
                    >
                        Cancel
                    </button>


                    <button
                        type="submit"
                        class="admin-faq-save"
                        id="faqModalSaveButton"
                    >

                        <i
                            id="faqModalSaveIcon"
                            class="fa-solid fa-plus"
                        ></i>


                        <span id="faqModalSaveText">
                            Add FAQ
                        </span>

                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection



@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const modal =
        document.getElementById('faqModal');

    const form =
        document.getElementById('faqModalForm');

    const modalTitle =
        document.getElementById('faqModalTitle');

    const modalSubtitle =
        document.getElementById('faqModalSubtitle');

    const modalHeaderIcon =
        document.getElementById('faqModalHeaderIcon');

    const saveButton =
        document.getElementById('faqModalSaveButton');

    const saveButtonIcon =
        document.getElementById('faqModalSaveIcon');

    const saveButtonText =
        document.getElementById('faqModalSaveText');

    const methodInput =
        document.getElementById('faqFormMethod');

    const modeInput =
        document.getElementById('faqFormMode');

    const faqIdInput =
        document.getElementById('faqFormId');

    const questionInput =
        document.getElementById('faqQuestion');

    const answerInput =
        document.getElementById('faqAnswer');

    const answerCount =
        document.getElementById('faqAnswerCount');

    const sortOrderInput =
        document.getElementById('faqSortOrder');

    const activeInput =
        document.getElementById('faqIsActive');

    const homepageInput =
        document.getElementById('faqShowOnHome');

    const createButton =
        document.getElementById('openCreateFaqModal');

    const emptyCreateButton =
        document.getElementById('openCreateFaqModalEmpty');

    const editButtons =
        document.querySelectorAll(
            '.admin-faq-edit-button'
        );

    const closeButtons =
        document.querySelectorAll(
            '[data-close-faq-modal]'
        );


    if (!modal || !form) {
        return;
    }


    const storeUrl =
        form.dataset.storeUrl;

    const updateUrlTemplate =
        form.dataset.updateUrl;

    const nextSortOrder =
        @json($nextSortOrder);


    /*
    |--------------------------------------------------------------------------
    | Answer Counter
    |--------------------------------------------------------------------------
    */

    function updateAnswerCounter() {

        if (!answerInput || !answerCount) {
            return;
        }

        answerCount.textContent =
            `${answerInput.value.length} / 10000`;

    }


    answerInput?.addEventListener(
        'input',
        updateAnswerCounter
    );


    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    function openModal() {

        modal.classList.add('show');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'admin-modal-open'
        );


        setTimeout(function () {

            questionInput?.focus();

        }, 120);

    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    function closeModal() {

        modal.classList.remove('show');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove(
            'admin-modal-open'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Create Mode
    |--------------------------------------------------------------------------
    */

    function setCreateMode() {

        form.action =
            storeUrl;


        methodInput.disabled =
            true;

        methodInput.value =
            '';


        modeInput.value =
            'create';

        faqIdInput.value =
            '';


        questionInput.value =
            '';

        answerInput.value =
            '';

        sortOrderInput.value =
            nextSortOrder;

        activeInput.checked =
            true;

        homepageInput.checked =
            false;


        modalTitle.textContent =
            'Add FAQ';

        modalSubtitle.textContent =
            'Create a new question for the public website.';

        modalHeaderIcon.className =
            'fa-solid fa-plus';

        saveButtonIcon.className =
            'fa-solid fa-plus';

        saveButtonText.textContent =
            'Add FAQ';


        updateAnswerCounter();

    }


    /*
    |--------------------------------------------------------------------------
    | Edit Mode
    |--------------------------------------------------------------------------
    */

    function setEditMode(faq) {

        const updateUrl =
            updateUrlTemplate.replace(
                '__FAQ__',
                faq.id
            );


        form.action =
            updateUrl;


        methodInput.disabled =
            false;

        methodInput.value =
            'PUT';


        modeInput.value =
            'edit';

        faqIdInput.value =
            faq.id;


        questionInput.value =
            faq.question ?? '';

        answerInput.value =
            faq.answer ?? '';

        sortOrderInput.value =
            faq.sort_order ?? 0;

        activeInput.checked =
            Boolean(
                faq.is_active
            );

        homepageInput.checked =
            Boolean(
                faq.show_on_home
            );


        modalTitle.textContent =
            'Edit FAQ';

        modalSubtitle.textContent =
            'Update this FAQ and its public display settings.';

        modalHeaderIcon.className =
            'fa-solid fa-pen';

        saveButtonIcon.className =
            'fa-solid fa-floppy-disk';

        saveButtonText.textContent =
            'Save Changes';


        updateAnswerCounter();

    }


    /*
    |--------------------------------------------------------------------------
    | Add FAQ Button
    |--------------------------------------------------------------------------
    */

    createButton?.addEventListener(
        'click',
        function () {

            setCreateMode();

            openModal();

        }
    );


    emptyCreateButton?.addEventListener(
        'click',
        function () {

            setCreateMode();

            openModal();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Edit Buttons
    |--------------------------------------------------------------------------
    */

    editButtons.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                try {

                    const encoded =
                        button.dataset.faq;

                    const faq =
                        JSON.parse(
                            atob(encoded)
                        );


                    setEditMode(faq);

                    openModal();

                } catch (error) {

                    console.error(
                        'Unable to load FAQ data.',
                        error
                    );

                }

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Close Buttons
    |--------------------------------------------------------------------------
    */

    closeButtons.forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                closeModal();

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Escape Key
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
                &&
                modal.classList.contains(
                    'show'
                )
            ) {

                closeModal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Prevent Double Submission
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function () {

            if (!form.checkValidity()) {
                return;
            }


            saveButton.disabled =
                true;


            saveButton.classList.add(
                'is-loading'
            );


            saveButtonText.textContent =
                modeInput.value === 'edit'
                    ? 'Saving...'
                    : 'Adding...';


            saveButtonIcon.className =
                'fa-solid fa-spinner fa-spin';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Restore Modal After Validation Error
    |--------------------------------------------------------------------------
    */

    @if($errors->any())

        const oldMode =
            @json(old('form_mode', 'create'));

        const oldFaqId =
            @json(old('faq_id'));

        const oldQuestion =
            @json(old('question'));

        const oldAnswer =
            @json(old('answer'));

        const oldSortOrder =
            @json(old('sort_order', $nextSortOrder));

        const oldActive =
            @json((int) old('is_active', 0));

        const oldHomepage =
            @json((int) old('show_on_home', 0));


        if (
            oldMode === 'edit'
            &&
            oldFaqId
        ) {

            setEditMode({

                id:
                    oldFaqId,

                question:
                    oldQuestion,

                answer:
                    oldAnswer,

                sort_order:
                    oldSortOrder,

                is_active:
                    Boolean(
                        Number(oldActive)
                    ),

                show_on_home:
                    Boolean(
                        Number(oldHomepage)
                    ),

            });

        } else {

            setCreateMode();


            questionInput.value =
                oldQuestion ?? '';

            answerInput.value =
                oldAnswer ?? '';

            sortOrderInput.value =
                oldSortOrder ?? nextSortOrder;

            activeInput.checked =
                Boolean(
                    Number(oldActive)
                );

            homepageInput.checked =
                Boolean(
                    Number(oldHomepage)
                );


            updateAnswerCounter();

        }


        openModal();

    @endif


    updateAnswerCounter();

});
</script>

@endpush