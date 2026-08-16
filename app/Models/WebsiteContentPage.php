<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteContentPage extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'slug',

        'meta_title',

        'meta_description',

        'content',

        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'content' =>
            'array',

    ];


    /*
    |--------------------------------------------------------------------------
    | Updating Admin
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
    | Get / Create Page
    |--------------------------------------------------------------------------
    */

    public static function page(
        string $slug
    ): self {
        $defaults =
            static::defaults(
                $slug
            );


        return static::firstOrCreate(

            [
                'slug' =>
                    $slug,
            ],

            [
                'meta_title' =>
                    $defaults['meta_title'],

                'meta_description' =>
                    $defaults['meta_description'],

                'content' =>
                    $defaults['content'],

                'updated_by' =>
                    null,
            ]

        );
    }


    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    public static function defaults(
        string $slug
    ): array {
        if (
            $slug === 'about'
        ) {
            return static::aboutDefaults();
        }


        if (
            $slug === 'how-it-works'
        ) {
            return static::howItWorksDefaults();
        }


        return [

            'meta_title' =>
                'MidPoint',

            'meta_description' =>
                '',

            'content' =>
                [],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | About Defaults
    |--------------------------------------------------------------------------
    */

    private static function aboutDefaults(): array
    {
        return [

            'meta_title' =>
                'About MidPoint — Safe Online Transactions',


            'meta_description' =>
                'Learn why MidPoint exists and how we help Nigerian buyers and sellers trade safely online.',


            'content' => [

                /*
                |--------------------------------------------------------------------------
                | Hero
                |--------------------------------------------------------------------------
                */

                'hero' => [

                    'eyebrow' =>
                        'About MidPoint',

                    'title' =>
                        'We exist because "what if they scam me?" kills good deals every day.',

                    'description' =>
                        "Millions of Nigerians find buyers and sellers on WhatsApp, Instagram, Facebook Marketplace, Jiji and through referrals. The products are real. The people are mostly honest. But without trust, deals collapse — or worse, someone loses money. MidPoint is the neutral middle: we hold the buyer's payment safely and only release it to the seller when the buyer confirms the item, or the inspection period expires.",

                ],


                /*
                |--------------------------------------------------------------------------
                | Statistics / Facts
                |--------------------------------------------------------------------------
                */

                'stats' => [

                    [
                        'label' =>
                            'Founded',

                        'value' =>
                            '2026',

                        'description' =>
                            'Lagos, Nigeria',
                    ],


                    [
                        'label' =>
                            'Mission',

                        'value' =>
                            'Make trust free',

                        'description' =>
                            'Between strangers who trade online',
                    ],


                    [
                        'label' =>
                            'We are not',

                        'value' =>
                            'A marketplace',

                        'description' =>
                            'Find your deal anywhere — protect it here',
                    ],

                ],


                /*
                |--------------------------------------------------------------------------
                | Principles
                |--------------------------------------------------------------------------
                */

                'principles_heading' =>
                    'Our principles',


                'principles' => [

                    [
                        'icon' =>
                            '⚖️',

                        'title' =>
                            'Neutrality.',

                        'description' =>
                            "We don't take sides. Funds move based on rules both parties agreed to upfront.",
                    ],


                    [
                        'icon' =>
                            '👁️',

                        'title' =>
                            'Transparency.',

                        'description' =>
                            "Sellers see exactly what they'll receive. Buyers see exactly what they'll pay. No surprises.",
                    ],


                    [
                        'icon' =>
                            '⚡',

                        'title' =>
                            'Speed.',

                        'description' =>
                            "The moment a buyer accepts an item, the seller's payout is on its way.",
                    ],


                    [
                        'icon' =>
                            '🇳🇬',

                        'title' =>
                            'Local reality.',

                        'description' =>
                            'Built around how Nigerians actually trade — POS-era pragmatism, WhatsApp-first deals, riders and park logistics.',
                    ],

                ],


                /*
                |--------------------------------------------------------------------------
                | CTA
                |--------------------------------------------------------------------------
                */

                'cta' => [

                    'title' =>
                        'Trade with confidence.',

                    'description' =>
                        'Bring your next online deal to MidPoint and protect both sides from the start.',

                    'button_text' =>
                        'Create free account',

                    'button_url' =>
                        '/register',

                ],

            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | How It Works Defaults
    |--------------------------------------------------------------------------
    */

    private static function howItWorksDefaults(): array
    {
        return [

            'meta_title' =>
                'How MidPoint Works — Buyer & Seller Protection',


            'meta_description' =>
                'See exactly how MidPoint protects buyers and sellers from payment through delivery, inspection and final release.',


            'content' => [

                /*
                |--------------------------------------------------------------------------
                | Hero
                |--------------------------------------------------------------------------
                */

                'hero' => [

                    'eyebrow' =>
                        'How MidPoint works',

                    'title' =>
                        'One flow. Both sides protected.',

                    'description' =>
                        "Whether you're the buyer or the seller, here's exactly what happens from start to finish.",

                ],


                /*
                |--------------------------------------------------------------------------
                | Seller Journey
                |--------------------------------------------------------------------------
                */

                'seller_badge' =>
                    'For sellers',


                'seller_steps' => [

                    [
                        'title' =>
                            'Create a transaction',

                        'description' =>
                            'Add the product, photos, price and quantity. Choose your delivery option.',
                    ],


                    [
                        'title' =>
                            'Share the invite link',

                        'description' =>
                            'Send it to your buyer on WhatsApp, Instagram DM — anywhere.',
                    ],


                    [
                        'title' =>
                            'Ship when payment is held',

                        'description' =>
                            "We notify you the moment the buyer's money is secured. Arrange your delivery, then mark the item as dispatched so the buyer knows it is on the way.",
                    ],


                    [
                        'title' =>
                            'Get paid',

                        'description' =>
                            'Once the buyer accepts, or the applicable inspection period completes, your payout is released according to the transaction rules.',
                    ],

                ],


                /*
                |--------------------------------------------------------------------------
                | Buyer Journey
                |--------------------------------------------------------------------------
                */

                'buyer_badge' =>
                    'For buyers',


                'buyer_steps' => [

                    [
                        'title' =>
                            "Open the seller's invite",

                        'description' =>
                            'Review the product, price and delivery details before committing.',
                    ],


                    [
                        'title' =>
                            'Pay MidPoint, not the seller',

                        'description' =>
                            "You pay the product price. Your money is held safely — the seller never touches it until the transaction's release conditions are satisfied.",
                    ],


                    [
                        'title' =>
                            'Receive and inspect',

                        'description' =>
                            'Receive the item and use the available inspection period to check it properly.',
                    ],


                    [
                        'title' =>
                            'Accept or dispute',

                        'description' =>
                            'Happy with the item? Accept it and release the funds. Something wrong? Open a dispute so the transaction remains protected while the issue is reviewed.',
                    ],

                ],


                /*
                |--------------------------------------------------------------------------
                | Delivery
                |--------------------------------------------------------------------------
                */

                'delivery_heading' =>
                    'Delivery',


                'delivery_cards' => [

                    [
                        'icon' =>
                            '📦',

                        'title' =>
                            'Sellers arrange their own delivery',

                        'badge' =>
                            '',

                        'description' =>
                            'Use your own rider, park logistics, a courier company or hand delivery — whatever works for your route. When the item leaves your hands, mark it as dispatched in your dashboard and the buyer is notified.',
                    ],


                    [
                        'icon' =>
                            '🚚',

                        'title' =>
                            'MidPoint Courier',

                        'badge' =>
                            'Coming soon',

                        'description' =>
                            "We're building an integrated courier experience so sellers can hand off delivery and buyers can follow delivery progress inside MidPoint.",
                    ],

                ],


                /*
                |--------------------------------------------------------------------------
                | CTA
                |--------------------------------------------------------------------------
                */

                'cta' => [

                    'title' =>
                        'Ready to protect your next deal?',

                    'description' =>
                        'Create your MidPoint account and use a clear transaction process that protects both buyer and seller.',

                    'button_text' =>
                        'Start a secure transaction',

                    'button_url' =>
                        '/register',

                ],

            ],

        ];
    }
}