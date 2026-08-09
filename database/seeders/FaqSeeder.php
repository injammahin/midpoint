<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run()
    {
        $faqs = [

            [
                'question' =>
                    'Is MidPoint a marketplace?',

                'answer' =>
                    "No. We don't list or sell products. You find your buyer or seller anywhere — WhatsApp, Instagram, Jiji, Facebook Marketplace, referrals — and use MidPoint only to protect the payment.",

                'sort_order' => 1,

                'show_on_home' => true,
            ],


            [
                'question' =>
                    "Who pays MidPoint's fee?",

                'answer' =>
                    "The seller pays a 5% service fee, deducted from their payout. Buyers pay nothing to MidPoint — only the product price agreed with the seller. Delivery is arranged directly between buyer and seller, so any delivery cost is settled between you — MidPoint doesn't charge or collect it.",

                'sort_order' => 2,

                'show_on_home' => true,
            ],


            [
                'question' =>
                    'What happens during the 8-hour inspection?',

                'answer' =>
                    "Once the item is delivered, the buyer has 8 hours to inspect it and either accept the item or open a dispute. If the buyer does nothing, funds are released automatically when the inspection window closes.",

                'sort_order' => 3,

                'show_on_home' => true,
            ],


            [
                'question' =>
                    "What if the item isn't as described?",

                'answer' =>
                    "Open a dispute before the inspection window closes. Both parties submit evidence — photos, chat records and delivery proof — and our resolution team reviews the case.",

                'sort_order' => 4,

                'show_on_home' => true,
            ],


            [
                'question' =>
                    'Who handles delivery?',

                'answer' =>
                    "Sellers arrange their own delivery using their own rider, park logistics, courier company or hand delivery. When the item is dispatched, the seller marks it as dispatched and the buyer is notified. Delivery costs are agreed directly between buyer and seller.",

                'sort_order' => 5,

                'show_on_home' => false,
            ],


            [
                'question' =>
                    'How fast do sellers get paid?',

                'answer' =>
                    "The moment the buyer accepts the item, or the inspection window expires without a dispute, the seller payout process can begin to the verified bank account.",

                'sort_order' => 6,

                'show_on_home' => false,
            ],


            [
                'question' =>
                    'If I return an item for a refund, who pays for the return delivery?',

                'answer' =>
                    "Where a return is required, the buyer arranges and pays for the return delivery and uploads proof of postage. The payment remains protected until the return process is confirmed.",

                'sort_order' => 7,

                'show_on_home' => false,
            ],


            [
                'question' =>
                    'What if my package takes longer than expected to arrive because the seller is far away?',

                'answer' =>
                    "The inspection period begins after the item is confirmed as delivered, so long-distance delivery does not reduce your inspection period.",

                'sort_order' => 8,

                'show_on_home' => false,
            ],


            [
                'question' =>
                    'What if I need more time to inspect the item?',

                'answer' =>
                    "Open a dispute before the inspection period expires if you have a genuine reason that prevents you from completing the inspection. This keeps the transaction under review instead of automatically releasing the payment.",

                'sort_order' => 9,

                'show_on_home' => false,
            ],


            [
                'question' =>
                    'Can I use MidPoint for services, not just goods?',

                'answer' =>
                    "Yes. MidPoint can support protected transactions involving physical goods and eligible services. For services, the delivery and inspection process applies to the completed work instead of a physical item.",

                'sort_order' => 10,

                'show_on_home' => false,
            ],

        ];


        foreach ($faqs as $faq) {

            Faq::updateOrCreate(

                [
                    'question' =>
                        $faq['question'],
                ],

                [
                    'answer' =>
                        $faq['answer'],

                    'sort_order' =>
                        $faq['sort_order'],

                    'is_active' =>
                        true,

                    'show_on_home' =>
                        $faq['show_on_home'],
                ]
            );
        }
    }
}