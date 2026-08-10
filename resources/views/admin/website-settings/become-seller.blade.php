@extends('admin.layouts.app')


@section(
    'title',
    'Seller Packages'
)


@section(
    'page-title',
    'Become Seller Page'
)


@section('content')


<div class="admin-module-header">

    <h2>
        Seller Packages
    </h2>

    <p>
        Create and manage packages available
        to verified MidPoint sellers.
    </p>

</div>



@if(session('success'))

    <div
        style="
            margin-bottom:16px;
            padding:12px 15px;
            border:1px solid #9ee6c1;
            border-radius:10px;
            background:#ecfdf3;
            color:#087443;
            font-size:13px;
            font-weight:600;
        "
    >
        {{ session('success') }}
    </div>

@endif



@if($errors->any())

    <div
        style="
            margin-bottom:16px;
            padding:14px;
            border:1px solid #fecaca;
            border-radius:10px;
            background:#fff1f2;
            color:#b42318;
        "
    >

        @foreach($errors->all() as $error)

            <div>
                {{ $error }}
            </div>

        @endforeach

    </div>

@endif



{{-- =========================================================
    CREATE PACKAGE
========================================================== --}}

<div
    class="admin-card"
    style="
        margin-bottom:20px;
        padding:22px;
    "
>

    <div
        style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:18px;
        "
    >

        <div>

            <h3
                style="
                    margin:0;
                    font-size:16px;
                "
            >
                Create Seller Package
            </h3>


            <p
                style="
                    margin:5px 0 0;
                    color:var(--admin-muted);
                    font-size:12px;
                "
            >
                Configure price, product limit and benefits.
            </p>

        </div>

    </div>


    <form
        method="POST"
        action="{{
            route(
                'admin.website-settings.seller-packages.store'
            )
        }}"
    >

        @csrf


        <div
            style="
                display:grid;
                grid-template-columns:repeat(3,minmax(0,1fr));
                gap:14px;
            "
        >

            <div class="seller-package-field">

                <label>
                    Package Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Starter"
                    required
                >

            </div>


            <div class="seller-package-field">

                <label>
                    Price (₦)
                </label>

                <input
                    type="number"
                    name="price"
                    value="{{ old('price') }}"
                    min="0"
                    step="0.01"
                    placeholder="5000"
                    required
                >

            </div>


            <div class="seller-package-field">

                <label>
                    Product Limit
                </label>

                <input
                    type="number"
                    name="product_limit"
                    value="{{ old('product_limit') }}"
                    min="1"
                    placeholder="5"
                    required
                >

            </div>


            <div class="seller-package-field">

                <label>
                    Billing
                </label>

                <select name="billing_period">

                    <option value="month">
                        Monthly
                    </option>

                    <option value="year">
                        Yearly
                    </option>

                </select>

            </div>


            <div class="seller-package-field">

                <label>
                    Card Theme
                </label>

                <select name="theme">

                    <option value="slate">
                        Slate
                    </option>

                    <option value="green">
                        Green
                    </option>

                    <option value="purple">
                        Purple
                    </option>

                </select>

            </div>


            <div class="seller-package-field">

                <label>
                    Sort Order
                </label>

                <input
                    type="number"
                    name="sort_order"
                    value="{{ old('sort_order', 0) }}"
                    min="0"
                >

            </div>

        </div>



        <div
            class="seller-package-field"
            style="margin-top:14px;"
        >

            <label>
                Short Description
            </label>

            <input
                type="text"
                name="description"
                value="{{ old('description') }}"
                placeholder="For new sellers testing the waters"
            >

        </div>



        <div
            class="seller-package-field"
            style="margin-top:14px;"
        >

            <label>
                Features
            </label>

            <textarea
                name="features"
                rows="5"
                placeholder="Verified badge & Featured listing&#10;Trust score on your profile&#10;Buyer reviews"
            >{{ old('features') }}</textarea>

            <small>
                Put one feature on each line.
                Product limit is automatically displayed separately.
            </small>

        </div>



        <div
            style="
                display:flex;
                gap:20px;
                margin-top:16px;
            "
        >

            <label class="seller-check">

                <input
                    type="checkbox"
                    name="is_popular"
                    value="1"
                >

                Most Popular

            </label>


            <label class="seller-check">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    checked
                >

                Active

            </label>

        </div>



        <button
            type="submit"
            class="seller-package-save"
        >

            <i class="fa-solid fa-plus"></i>

            Create Package

        </button>

    </form>

</div>



{{-- =========================================================
    EXISTING PACKAGES
========================================================== --}}

<div
    style="
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:16px;
    "
>

    @forelse($packages as $package)

        <div
            class="admin-card"
            style="padding:20px;"
        >

            <div
                style="
                    display:flex;
                    justify-content:space-between;
                    gap:10px;
                    margin-bottom:15px;
                "
            >

                <div>

                    <strong
                        style="
                            display:block;
                            font-size:16px;
                        "
                    >
                        {{ $package->name }}
                    </strong>


                    <span
                        style="
                            font-size:11px;
                            color:var(--admin-muted);
                        "
                    >
                        {{
                            $package->is_active
                                ? 'Active'
                                : 'Hidden'
                        }}
                    </span>

                </div>


                @if($package->is_popular)

                    <span
                        style="
                            padding:5px 8px;
                            border-radius:20px;
                            background:#e8f8ef;
                            color:#087443;
                            font-size:10px;
                            font-weight:700;
                        "
                    >
                        Popular
                    </span>

                @endif

            </div>



            <form
                method="POST"
                action="{{
                    route(
                        'admin.website-settings.seller-packages.update',
                        $package
                    )
                }}"
            >

                @csrf
                @method('PUT')


                <div class="seller-package-field">

                    <label>
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ $package->name }}"
                        required
                    >

                </div>


                <div
                    style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:10px;
                    "
                >

                    <div class="seller-package-field">

                        <label>
                            Price
                        </label>

                        <input
                            type="number"
                            name="price"
                            value="{{
                                $package->price
                            }}"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>


                    <div class="seller-package-field">

                        <label>
                            Product Limit
                        </label>

                        <input
                            type="number"
                            name="product_limit"
                            value="{{
                                $package->product_limit
                            }}"
                            min="1"
                            required
                        >

                    </div>

                </div>


                <div
                    style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:10px;
                    "
                >

                    <div class="seller-package-field">

                        <label>
                            Billing
                        </label>

                        <select
                            name="billing_period"
                        >

                            <option
                                value="month"
                                {{
                                    $package->billing_period === 'month'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Monthly
                            </option>

                            <option
                                value="year"
                                {{
                                    $package->billing_period === 'year'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Yearly
                            </option>

                        </select>

                    </div>


                    <div class="seller-package-field">

                        <label>
                            Theme
                        </label>

                        <select name="theme">

                            @foreach([
                                'slate',
                                'green',
                                'purple'
                            ] as $theme)

                                <option
                                    value="{{ $theme }}"
                                    {{
                                        $package->theme === $theme
                                            ? 'selected'
                                            : ''
                                    }}
                                >
                                    {{ ucfirst($theme) }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                <div class="seller-package-field">

                    <label>
                        Description
                    </label>

                    <input
                        type="text"
                        name="description"
                        value="{{
                            $package->description
                        }}"
                    >

                </div>


                <div class="seller-package-field">

                    <label>
                        Features
                    </label>

                    <textarea
                        name="features"
                        rows="5"
                    >{{ implode("\n", $package->features ?? []) }}</textarea>

                </div>


                <div class="seller-package-field">

                    <label>
                        Sort Order
                    </label>

                    <input
                        type="number"
                        name="sort_order"
                        value="{{
                            $package->sort_order
                        }}"
                        min="0"
                    >

                </div>


                <label class="seller-check">

                    <input
                        type="checkbox"
                        name="is_popular"
                        value="1"
                        {{
                            $package->is_popular
                                ? 'checked'
                                : ''
                        }}
                    >

                    Most Popular

                </label>


                <label class="seller-check">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{
                            $package->is_active
                                ? 'checked'
                                : ''
                        }}
                    >

                    Active

                </label>


                <button
                    type="submit"
                    class="seller-package-save"
                >
                    Save Changes
                </button>

            </form>



            <div
                style="
                    display:flex;
                    gap:8px;
                    margin-top:9px;
                "
            >

                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.website-settings.seller-packages.toggle',
                            $package
                        )
                    }}"
                    style="flex:1;"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="seller-secondary-button"
                    >
                        {{
                            $package->is_active
                                ? 'Hide'
                                : 'Activate'
                        }}
                    </button>

                </form>


                <form
                    method="POST"
                    action="{{
                        route(
                            'admin.website-settings.seller-packages.destroy',
                            $package
                        )
                    }}"
                    onsubmit="
                        return confirm(
                            'Delete this package?'
                        );
                    "
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="seller-delete-button"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>

                </form>

            </div>

        </div>

    @empty

        <div class="admin-card">
            No seller packages have been created.
        </div>

    @endforelse

</div>



@push('styles')

<style>

.seller-package-field {
    display:flex;
    flex-direction:column;
    gap:6px;
    margin-bottom:12px;
}

.seller-package-field label {
    color:var(--admin-heading);
    font-size:11px;
    font-weight:600;
}

.seller-package-field input,
.seller-package-field select,
.seller-package-field textarea {
    width:100%;
    padding:10px 11px;
    border:1px solid var(--admin-border);
    border-radius:9px;
    background:var(--admin-surface-soft);
    color:var(--admin-text);
    font-family:inherit;
    font-size:12px;
    outline:none;
}

.seller-package-field input:focus,
.seller-package-field select:focus,
.seller-package-field textarea:focus {
    border-color:var(--admin-accent);
}

.seller-package-field small {
    color:var(--admin-muted);
    font-size:10px;
}

.seller-check {
    display:inline-flex;
    align-items:center;
    gap:7px;
    color:var(--admin-text);
    font-size:11px;
}

.seller-package-save {
    width:100%;
    margin-top:16px;
    padding:11px;
    border:0;
    border-radius:9px;
    background:var(--admin-accent-strong);
    color:#052e2b;
    font-weight:700;
    cursor:pointer;
}

.seller-secondary-button {
    width:100%;
    padding:9px;
    border:1px solid var(--admin-border);
    border-radius:8px;
    background:var(--admin-surface-soft);
    color:var(--admin-heading);
    cursor:pointer;
}

.seller-delete-button {
    padding:9px 12px;
    border:1px solid rgba(217,45,32,.2);
    border-radius:8px;
    background:rgba(217,45,32,.08);
    color:#d92d20;
    cursor:pointer;
}

@media(max-width:1000px) {

    div[style*="grid-template-columns:repeat(3"] {
        grid-template-columns:1fr !important;
    }

}

</style>

@endpush


@endsection