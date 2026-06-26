<?php

namespace Database\Seeders;

use App\Models\CustomerReview;
use App\Models\Faq;
use App\Models\OfferAndPromo;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFaqs();
        $this->seedReviews();
        $this->seedPromos();
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'How do I book a bus ticket?',
                'answer' => 'You can book a bus ticket online through our website or mobile app, or visit any of our counters across Bangladesh.',
            ],
            [
                'question' => 'Can I cancel my ticket after booking?',
                'answer' => 'Yes, tickets can be cancelled up to 2 hours before departure. Cancellation charges may apply depending on the time of cancellation.',
            ],
            [
                'question' => 'What documents do I need to carry during the journey?',
                'answer' => 'Please carry your ticket (printed or digital) along with a valid photo ID (NID, passport, or driving license).',
            ],
            [
                'question' => 'Is there a luggage allowance?',
                'answer' => 'Each passenger is allowed to carry one piece of luggage up to 20 kg. Additional luggage may incur extra charges.',
            ],
            [
                'question' => 'Are AC buses available on all routes?',
                'answer' => 'AC buses are available on major routes including Dhaka–Chittagong and Dhaka–Sylhet. Check availability while booking.',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create(array_merge($faq, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }

    private function seedReviews(): void
    {
        $reviews = [
            [
                'name' => 'Md. Ariful Islam',
                'date' => '2026-01-10',
                'comment' => 'Excellent service! The bus was clean, on time, and the staff was very helpful. Highly recommend Imperial Bus for long trips.',
                'rating' => 5,
            ],
            [
                'name' => 'Fatema Akter',
                'date' => '2026-02-05',
                'comment' => 'Comfortable seats and smooth ride from Dhaka to Sylhet. The AC worked perfectly. Will book again.',
                'rating' => 5,
            ],
            [
                'name' => 'Rahim Chowdhury',
                'date' => '2026-03-15',
                'comment' => 'Good experience overall. The bus departed on time and arrived safely. Online booking was very easy.',
                'rating' => 4,
            ],
        ];

        foreach ($reviews as $review) {
            CustomerReview::create(array_merge($review, [
                'status' => 1,
                'created_by' => 1,
            ]));
        }
    }

    private function seedPromos(): void
    {
        $promos = [
            [
                'title' => 'Eid Special Offer',
                'description' => 'Get 15% discount on all AC bus tickets during Eid season. Book early to secure your seat!',
                'expired_date' => '2026-12-31',
                'image' => 'promos/eid-offer.jpg',
                'status' => 1,
            ],
            [
                'title' => 'First Time Booking Discount',
                'description' => 'New customers get BDT 50 off on their first online booking. Use code WELCOME50.',
                'expired_date' => '2026-12-31',
                'image' => 'promos/welcome-offer.jpg',
                'status' => 1,
            ],
        ];

        foreach ($promos as $promo) {
            OfferAndPromo::create(array_merge($promo, [
                'created_by' => 1,
            ]));
        }
    }
}
