<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FaqController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FAQ Management
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Faq::query();


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'question',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'answer',
                    'like',
                    "%{$search}%"
                );

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->status === 'active') {

            $query->where(
                'is_active',
                true
            );

        } elseif ($request->status === 'inactive') {

            $query->where(
                'is_active',
                false
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Homepage Filter
        |--------------------------------------------------------------------------
        */

        if ($request->home === 'yes') {

            $query->where(
                'show_on_home',
                true
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FAQ List
        |--------------------------------------------------------------------------
        */

        $faqs = $query
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Next Display Order
        |--------------------------------------------------------------------------
        */

        $nextSortOrder =
            (Faq::max('sort_order') ?? 0) + 1;


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                Faq::count(),

            'active' =>
                Faq::where(
                    'is_active',
                    true
                )->count(),

            'inactive' =>
                Faq::where(
                    'is_active',
                    false
                )->count(),

            'homepage' =>
                Faq::where(
                    'is_active',
                    true
                )
                ->where(
                    'show_on_home',
                    true
                )
                ->count(),

        ];


        return view(
            'admin.website-settings.faqs.index',
            compact(
                'faqs',
                'stats',
                'nextSortOrder'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store FAQ
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $this->validateFaq(
            $request
        );


        Faq::create([

            'question' =>
                $validated['question'],

            'answer' =>
                $validated['answer'],

            'sort_order' =>
                $validated['sort_order'],

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),

            'show_on_home' =>
                $request->boolean(
                    'show_on_home'
                ),

            'created_by' =>
                Auth::id(),

            'updated_by' =>
                Auth::id(),

        ]);


        return redirect()
            ->route(
                'admin.website-settings.faqs'
            )
            ->with(
                'success',
                'FAQ added successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update FAQ
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Faq $faq
    ) {

        $validated = $this->validateFaq(
            $request,
            $faq
        );


        $faq->update([

            'question' =>
                $validated['question'],

            'answer' =>
                $validated['answer'],

            'sort_order' =>
                $validated['sort_order'],

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),

            'show_on_home' =>
                $request->boolean(
                    'show_on_home'
                ),

            'updated_by' =>
                Auth::id(),

        ]);


        return redirect()
            ->route(
                'admin.website-settings.faqs'
            )
            ->with(
                'success',
                'FAQ updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Active Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(Faq $faq)
    {
        $faq->update([

            'is_active' =>
                !$faq->is_active,

            'updated_by' =>
                Auth::id(),

        ]);


        return back()->with(
            'success',
            $faq->is_active
                ? 'FAQ activated successfully.'
                : 'FAQ deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete FAQ
    |--------------------------------------------------------------------------
    */

    public function destroy(Faq $faq)
    {
        $faq->delete();


        return back()->with(
            'success',
            'FAQ deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateFaq(
        Request $request,
        ?Faq $faq = null
    ): array {

        return $request->validate(
            [

                'question' => [

                    'required',

                    'string',

                    'max:500',

                    Rule::unique(
                        'faqs',
                        'question'
                    )
                        ->whereNull(
                            'deleted_at'
                        )
                        ->ignore(
                            $faq?->id
                        ),

                ],


                'answer' => [
                    'required',
                    'string',
                    'max:10000',
                ],


                'sort_order' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:9999',
                ],


                'is_active' => [
                    'nullable',
                    'boolean',
                ],


                'show_on_home' => [
                    'nullable',
                    'boolean',
                ],

            ],
            [

                'question.required' =>
                    'Please enter the FAQ question.',

                'question.unique' =>
                    'This FAQ question already exists.',

                'answer.required' =>
                    'Please enter the FAQ answer.',

                'sort_order.required' =>
                    'Please enter the display order.',

            ]
        );
    }
}