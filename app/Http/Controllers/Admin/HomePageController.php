<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePageSetting;
use App\Models\HomeTestimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Home Page Admin Screen
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $settings =
            HomePageSetting::current();


        $testimonials =
            HomeTestimonial::query()
                ->ordered()
                ->get();


        return view(
            'admin.website-settings.home-page',
            compact(
                'settings',
                'testimonials'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Hero + Stats
    |--------------------------------------------------------------------------
    */

    public function updateHero(
        Request $request
    ) {
        $validated =
            $request->validate([

                'hero_badge' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'hero_title_before' => [
                    'required',
                    'string',
                    'max:180',
                ],

                'hero_title_highlight' => [
                    'required',
                    'string',
                    'max:120',
                ],

                'hero_title_after' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'hero_description' => [
                    'required',
                    'string',
                    'max:1200',
                ],

                'hero_primary_button_text' => [
                    'required',
                    'string',
                    'max:80',
                ],

                'hero_primary_button_url' => [
                    'required',
                    'string',
                    'max:500',
                ],

                'hero_secondary_button_text' => [
                    'required',
                    'string',
                    'max:80',
                ],

                'hero_secondary_button_url' => [
                    'required',
                    'string',
                    'max:500',
                ],


                'stat_one_value' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'stat_one_label' => [
                    'required',
                    'string',
                    'max:100',
                ],


                'stat_two_value' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'stat_two_label' => [
                    'required',
                    'string',
                    'max:100',
                ],


                'stat_three_value' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'stat_three_label' => [
                    'required',
                    'string',
                    'max:100',
                ],

            ]);


        $this->updateSettings(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'hero',
                ]
            )
            ->with(
                'success',
                'Hero and statistics updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Three Steps
    |--------------------------------------------------------------------------
    */

    public function updateSteps(
        Request $request
    ) {
        $validated =
            $request->validate([

                'steps_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'steps_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'steps_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'step_one_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'step_one_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'step_two_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'step_two_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'step_three_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'step_three_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],

            ]);


        $this->updateSettings(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'steps',
                ]
            )
            ->with(
                'success',
                'Three simple steps section updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Why MidPoint
    |--------------------------------------------------------------------------
    */

    public function updateWhy(
        Request $request
    ) {
        $validated =
            $request->validate([

                'why_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'why_title' => [
                    'required',
                    'string',
                    'max:255',
                ],


                'why_one_icon' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'why_one_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'why_one_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'why_two_icon' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'why_two_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'why_two_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'why_three_icon' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'why_three_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'why_three_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],


                'why_four_icon' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'why_four_title' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'why_four_description' => [
                    'required',
                    'string',
                    'max:1000',
                ],

            ]);


        $this->updateSettings(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'why',
                ]
            )
            ->with(
                'success',
                'Why Midpoint section updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Other Homepage Content
    |--------------------------------------------------------------------------
    */

    public function updateOther(
        Request $request
    ) {
        $validated =
            $request->validate([

                'featured_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'featured_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'featured_view_all_text' => [
                    'required',
                    'string',
                    'max:80',
                ],


                'faq_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'faq_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'faq_view_all_text' => [
                    'required',
                    'string',
                    'max:80',
                ],


                'final_cta_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'final_cta_description' => [
                    'required',
                    'string',
                    'max:1200',
                ],

                'final_cta_button_text' => [
                    'required',
                    'string',
                    'max:80',
                ],

                'final_cta_button_url' => [
                    'required',
                    'string',
                    'max:500',
                ],

            ]);


        $this->updateSettings(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'other',
                ]
            )
            ->with(
                'success',
                'Homepage supporting content updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Testimonial Section Heading
    |--------------------------------------------------------------------------
    */

    public function updateTestimonialSection(
        Request $request
    ) {
        $validated =
            $request->validate([

                'testimonials_eyebrow' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'testimonials_title' => [
                    'required',
                    'string',
                    'max:255',
                ],

            ]);


        $this->updateSettings(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'testimonials',
                ]
            )
            ->with(
                'success',
                'Testimonials section heading updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Testimonial
    |--------------------------------------------------------------------------
    */

    public function storeTestimonial(
        Request $request
    ) {
        $validated =
            $this->validateTestimonial(
                $request
            );


        $validated['is_active'] =
            $request->boolean(
                'is_active'
            );


        $validated['updated_by'] =
            Auth::id();


        HomeTestimonial::create(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'testimonials',
                ]
            )
            ->with(
                'success',
                'Testimonial added successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Testimonial
    |--------------------------------------------------------------------------
    */

    public function updateTestimonial(
        Request $request,
        HomeTestimonial $testimonial
    ) {
        $validated =
            $this->validateTestimonial(
                $request
            );


        $validated['is_active'] =
            $request->boolean(
                'is_active'
            );


        $validated['updated_by'] =
            Auth::id();


        $testimonial->update(
            $validated
        );


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'testimonials',
                ]
            )
            ->with(
                'success',
                'Testimonial updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Testimonial
    |--------------------------------------------------------------------------
    */

    public function toggleTestimonial(
        HomeTestimonial $testimonial
    ) {
        $testimonial->update([

            'is_active' =>
                !$testimonial->is_active,

            'updated_by' =>
                Auth::id(),

        ]);


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'testimonials',
                ]
            )
            ->with(
                'success',
                'Testimonial visibility updated.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Testimonial
    |--------------------------------------------------------------------------
    */

    public function destroyTestimonial(
        HomeTestimonial $testimonial
    ) {
        $testimonial->delete();


        return redirect()
            ->route(
                'admin.website-settings.home-page',
                [
                    'tab' =>
                        'testimonials',
                ]
            )
            ->with(
                'success',
                'Testimonial deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Testimonial Validation
    |--------------------------------------------------------------------------
    */

    private function validateTestimonial(
        Request $request
    ): array {
        return $request->validate([

            'reviewer_name' => [
                'required',
                'string',
                'max:120',
            ],

            'reviewer_meta' => [
                'required',
                'string',
                'max:180',
            ],

            'review_text' => [
                'required',
                'string',
                'max:1600',
            ],

            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'avatar_initials' => [
                'nullable',
                'string',
                'max:4',
            ],

            'avatar_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Save Singleton Settings
    |--------------------------------------------------------------------------
    */

    private function updateSettings(
        array $data
    ): void {
        DB::transaction(
            function () use ($data) {

                $settings =
                    HomePageSetting::current();


                $settings->update(
                    array_merge(
                        $data,
                        [
                            'updated_by' =>
                                Auth::id(),
                        ]
                    )
                );
            }
        );
    }
}