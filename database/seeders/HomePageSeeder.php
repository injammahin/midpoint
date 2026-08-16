<?php

namespace Database\Seeders;

use App\Models\HomePageSetting;
use App\Models\HomeTestimonial;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Default Homepage Content
        |--------------------------------------------------------------------------
        */

        HomePageSetting::updateOrCreate(

            [
                'id' => 1,
            ],

            HomePageSetting::defaults()

        );


        /*
        |--------------------------------------------------------------------------
        | Do Not Duplicate Testimonials
        |--------------------------------------------------------------------------
        */

        if (
            HomeTestimonial::query()
                ->count()
            >
            0
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Demo Testimonials
        |--------------------------------------------------------------------------
        |
        | These are development/demo seed records.
        | Replace them with genuine customer testimonials before production.
        |
        */

        $testimonials = [

            [
                'reviewer_name' =>
                    'Adaeze Bello',

                'reviewer_meta' =>
                    'Crowned Hair Empire, Lagos',

                'review_text' =>
                    "I sell wigs on Instagram. Buyers used to vanish after 'I'll transfer now now'. With Midpoint I ship only when the money is already held. Game changer.",

                'rating' =>
                    5,

                'avatar_initials' =>
                    'AB',

                'avatar_color' =>
                    '#7A5AF8',

                'sort_order' =>
                    10,
            ],


            [
                'reviewer_name' =>
                    'Kunle Ogunleye',

                'reviewer_meta' =>
                    'Buyer, Abeokuta',

                'review_text' =>
                    'Bought a UK-used laptop from a seller in Ibadan. The inspection window let me test everything before the money was released. Both of us were calm.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'KO',

                'avatar_color' =>
                    '#12B76A',

                'sort_order' =>
                    20,
            ],


            [
                'reviewer_name' =>
                    'Tunde Usman',

                'reviewer_meta' =>
                    'Electronics seller, Ikeja',

                'review_text' =>
                    "I dispatch with my own rider and mark the item as dispatched. The buyer gets notified instantly, and I know the payment is already protected.",

                'rating' =>
                    5,

                'avatar_initials' =>
                    'TU',

                'avatar_color' =>
                    '#F79009',

                'sort_order' =>
                    30,
            ],


            [
                'reviewer_name' =>
                    'Chiamaka Nwosu',

                'reviewer_meta' =>
                    'Buyer, Ibadan',

                'review_text' =>
                    'The seller and I had never met before, but the Midpoint flow made the transaction feel structured. I could inspect the item before confirming release.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'CN',

                'avatar_color' =>
                    '#175CD3',

                'sort_order' =>
                    40,
            ],


            [
                'reviewer_name' =>
                    'Fatima Abdullahi',

                'reviewer_meta' =>
                    'Fashion seller, Abuja',

                'review_text' =>
                    'My customers are more comfortable paying because they can see that the money is protected while I prepare and dispatch their order.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'FA',

                'avatar_color' =>
                    '#9E165F',

                'sort_order' =>
                    50,
            ],


            [
                'reviewer_name' =>
                    'Emeka Okafor',

                'reviewer_meta' =>
                    'Buyer, Enugu',

                'review_text' =>
                    'I liked that payment confirmation, dispatch and acceptance were all clear. There was no need to keep asking whether the seller had seen my transfer.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'EO',

                'avatar_color' =>
                    '#0E7090',

                'sort_order' =>
                    60,
            ],


            [
                'reviewer_name' =>
                    'Bisi Adeyemi',

                'reviewer_meta' =>
                    'Beauty seller, Lagos',

                'review_text' =>
                    'Midpoint gives both sides a process to follow. I can focus on packing the order instead of worrying about fake payment alerts.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'BA',

                'avatar_color' =>
                    '#6938EF',

                'sort_order' =>
                    70,
            ],


            [
                'reviewer_name' =>
                    'Sani Musa',

                'reviewer_meta' =>
                    'Buyer, Kaduna',

                'review_text' =>
                    'The product arrived as described and I confirmed it after checking everything. The whole transaction was easy to understand from start to finish.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'SM',

                'avatar_color' =>
                    '#027A48',

                'sort_order' =>
                    80,
            ],


            [
                'reviewer_name' =>
                    'Ifeoma Eze',

                'reviewer_meta' =>
                    'Accessories seller, Port Harcourt',

                'review_text' =>
                    'Customers who are buying from me for the first time feel more confident when I send them a Midpoint transaction link.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'IE',

                'avatar_color' =>
                    '#C4320A',

                'sort_order' =>
                    90,
            ],


            [
                'reviewer_name' =>
                    'David Akinola',

                'reviewer_meta' =>
                    'Buyer, Lagos',

                'review_text' =>
                    'The biggest difference for me is that I do not have to choose between trusting a stranger and walking away from a good deal.',

                'rating' =>
                    5,

                'avatar_initials' =>
                    'DA',

                'avatar_color' =>
                    '#3538CD',

                'sort_order' =>
                    100,
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | Insert Testimonials
        |--------------------------------------------------------------------------
        */

        foreach (
            $testimonials
            as
            $testimonial
        ) {

            HomeTestimonial::create(
                array_merge(
                    $testimonial,
                    [

                        'is_active' =>
                            true,

                        'updated_by' =>
                            null,

                    ]
                )
            );

        }
    }
}