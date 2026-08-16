<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePageSetting extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'hero_badge',

        'hero_title_before',

        'hero_title_highlight',

        'hero_title_after',

        'hero_description',

        'hero_primary_button_text',

        'hero_primary_button_url',

        'hero_secondary_button_text',

        'hero_secondary_button_url',


        'stat_one_value',

        'stat_one_label',

        'stat_two_value',

        'stat_two_label',

        'stat_three_value',

        'stat_three_label',


        'steps_eyebrow',

        'steps_title',

        'steps_description',


        'step_one_title',

        'step_one_description',


        'step_two_title',

        'step_two_description',


        'step_three_title',

        'step_three_description',


        'why_eyebrow',

        'why_title',


        'why_one_icon',

        'why_one_title',

        'why_one_description',


        'why_two_icon',

        'why_two_title',

        'why_two_description',


        'why_three_icon',

        'why_three_title',

        'why_three_description',


        'why_four_icon',

        'why_four_title',

        'why_four_description',


        'featured_eyebrow',

        'featured_title',

        'featured_view_all_text',


        'testimonials_eyebrow',

        'testimonials_title',


        'faq_eyebrow',

        'faq_title',

        'faq_view_all_text',


        'final_cta_title',

        'final_cta_description',

        'final_cta_button_text',

        'final_cta_button_url',


        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Updated By User
    |--------------------------------------------------------------------------
    */

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Default Home Page Content
    |--------------------------------------------------------------------------
    */

    public static function defaults(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Hero
            |--------------------------------------------------------------------------
            */

            'hero_badge' =>
                'Secure access control',


            'hero_title_before' =>
                'The safe middle for every',


            'hero_title_highlight' =>
                'online deal',


            'hero_title_after' =>
                '.',


            'hero_description' =>
                "Found a seller on WhatsApp, Instagram or Jiji? Don't pay directly. Midpoint holds the money until you confirm your item, so nobody gets burned.",


            'hero_primary_button_text' =>
                'Start a secure transaction',


            'hero_primary_button_url' =>
                '/register',


            'hero_secondary_button_text' =>
                'See how it works',


            'hero_secondary_button_url' =>
                '/how-it-works',


            /*
            |--------------------------------------------------------------------------
            | Stats
            |--------------------------------------------------------------------------
            */

            'stat_one_value' =>
                '₦184M+',


            'stat_one_label' =>
                'Safely held & released',


            'stat_two_value' =>
                '12,400+',


            'stat_two_label' =>
                'Completed transactions',


            'stat_three_value' =>
                '8 hrs',


            'stat_three_label' =>
                'Buyer inspection window',


            /*
            |--------------------------------------------------------------------------
            | Steps
            |--------------------------------------------------------------------------
            */

            'steps_eyebrow' =>
                'Three simple steps',


            'steps_title' =>
                'From "is this seller legit?" to done deal.',


            'steps_description' =>
                "Midpoint isn't a marketplace. Find your buyer or seller anywhere, then bring the payment here.",


            'step_one_title' =>
                'Create the transaction',


            'step_one_description' =>
                'The seller lists the item, price and delivery option, then shares a secure invite link with the buyer.',


            'step_two_title' =>
                'Buyer pays Midpoint',


            'step_two_description' =>
                "The buyer pays into Midpoint's secure hold, not the seller's account. The seller ships knowing the money is real.",


            'step_three_title' =>
                'Inspect, then release',


            'step_three_description' =>
                'The buyer gets 8 hours to inspect. Accept the item and funds go to the seller instantly, or open a dispute.',


            /*
            |--------------------------------------------------------------------------
            | Why MidPoint
            |--------------------------------------------------------------------------
            */

            'why_eyebrow' =>
                'Why Midpoint',


            'why_title' =>
                'Built for how Nigerians actually buy and sell.',


            'why_one_icon' =>
                '🛡️',


            'why_one_title' =>
                'No "pay before delivery" fear',


            'why_one_description' =>
                'Money only moves when both sides are protected.',


            'why_two_icon' =>
                '📦',


            'why_two_title' =>
                'Dispatch confirmation',


            'why_two_description' =>
                "Sellers arrange their own delivery and mark the item as dispatched, so you always know when it's on the way.",


            'why_three_icon' =>
                '⏱️',


            'why_three_title' =>
                '8-hour inspection',


            'why_three_description' =>
                'Open the box, test it, be sure before a single naira is released.',


            'why_four_icon' =>
                '⚖️',


            'why_four_title' =>
                'Fair dispute resolution',


            'why_four_description' =>
                "If something's wrong, our resolution team steps in with evidence from both sides.",


            /*
            |--------------------------------------------------------------------------
            | Featured Businesses
            |--------------------------------------------------------------------------
            */

            'featured_eyebrow' =>
                'Featured businesses',


            'featured_title' =>
                'Verified sellers who trade the safe way.',


            'featured_view_all_text' =>
                'View all',


            /*
            |--------------------------------------------------------------------------
            | Testimonials
            |--------------------------------------------------------------------------
            */

            'testimonials_eyebrow' =>
                'Testimonials',


            'testimonials_title' =>
                'People sleep better with Midpoint.',


            /*
            |--------------------------------------------------------------------------
            | FAQ
            |--------------------------------------------------------------------------
            */

            'faq_eyebrow' =>
                'FAQs',


            'faq_title' =>
                'Questions people ask before their first deal.',


            'faq_view_all_text' =>
                'See all FAQs',


            /*
            |--------------------------------------------------------------------------
            | Final CTA
            |--------------------------------------------------------------------------
            */

            'final_cta_title' =>
                'Buy with confidence. Sell with confidence.',


            'final_cta_description' =>
                "Your next online deal doesn't have to be a gamble. It takes 2 minutes to create your first secure transaction.",


            'final_cta_button_text' =>
                'Create free account',


            'final_cta_button_url' =>
                '/register',


            'updated_by' =>
                null,

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Get Singleton Home Page Setting
    |--------------------------------------------------------------------------
    */

    public static function current(): self
    {
        return static::firstOrCreate(
            [
                'id' => 1,
            ],
            static::defaults()
        );
    }
}