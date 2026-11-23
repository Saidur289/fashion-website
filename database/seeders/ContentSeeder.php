<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPages();
        $this->seedFaqs();
        $this->seedTeam();
        $this->seedBlog();
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'About SafeBuy',
                'subtitle' => 'Safe to Buy, Smart to Trust — 100% Authentic K-Beauty & Smart Gadgets in Bangladesh.',
                'body' => <<<'MD'
## Our Story
SafeBuy was founded with one simple promise: shoppers in Bangladesh deserve **100% genuine Korean skincare, Japanese beauty essentials, and smart everyday gadgets** — without worrying about counterfeit products or inflated grey-market prices.

Every product on SafeBuy is sourced directly from authorized distributors and verified suppliers in South Korea, Japan, and trusted global partners. Before anything reaches your doorstep, our quality team inspects batch codes, factory seals, and expiry dates.

## Why Shoppers Trust SafeBuy
- **100% Authenticity Guarantee:** Every item is batch-verified and backed by our authenticity promise.
- **Nationwide Cash on Delivery & bKash:** Fast delivery across Dhaka and all 64 districts of Bangladesh.
- **SafeBuy Concierge:** Looking for a specific Korean serum or gadget not listed in our shop? Request it through our Concierge service and we will source it for you.
- **Real Support:** Honest skin-type guidance and responsive after-sales support whenever you need help.
MD,
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & Conditions',
                'subtitle' => 'Please read these terms carefully before placing an order on SafeBuy.',
                'body' => <<<'MD'
## 1. General
By accessing and placing an order on SafeBuy, you agree to the terms below. We reserve the right to update product prices, offers, and policies without prior notice, though confirmed orders always keep the price shown at checkout.

## 2. Product Authenticity & Descriptions
We guarantee that all beauty and gadget products sold on SafeBuy are 100% original. Product packaging may occasionally be updated by the manufacturer in Korea or Japan; ingredients and authenticity remain verified.

## 3. Orders & Confirmation
- After placing an order, our team may call or SMS your mobile number to confirm delivery details.
- For **bKash** payments, orders are processed once the Transaction ID is verified.
- SafeBuy reserves the right to cancel suspected fake or abusive orders.

## 4. Pricing & Coupons
All prices are in Bangladeshi Taka (Tk). Only one coupon code may be applied per order unless otherwise specified.
MD,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'subtitle' => 'How SafeBuy collects, uses, and protects your personal information.',
                'body' => <<<'MD'
## 1. Information We Collect
When you order, create an account, or contact us, we collect only the details needed to serve you: your name, mobile number, delivery address, optional email address, and order history.

## 2. How We Use Your Information
- To confirm, pack, ship, and track your orders across Bangladesh.
- To send order status updates via SMS or email.
- To verify bKash payments and prevent fraudulent orders.
- To send offers only if you subscribe to our newsletter (you can unsubscribe anytime).

## 3. Data Security & Sharing
We **never sell or rent** your phone number or personal information. We only share your delivery name, phone, and address with our courier partner to complete delivery.

## 4. Contact Us
If you wish to update or delete your account details, reach out anytime via our Contact page or email us.
MD,
            ],
            [
                'slug' => 'shipping-returns',
                'title' => 'Shipping & Returns',
                'subtitle' => 'Fast nationwide delivery across Bangladesh and hassle-free returns on damaged or wrong items.',
                'body' => <<<'MD'
## Delivery Time & Charges
- **Inside Dhaka:** 1–2 working days (standard charge Tk 70).
- **Outside Dhaka (All 64 Districts):** 2–4 working days (standard charge Tk 130).
- **Free Delivery:** Orders meeting our free-shipping threshold ship completely free nationwide.

## Checking Your Parcel on Delivery
We encourage you to inspect the outer parcel condition in front of the delivery rider. If anything looks tampered with or broken, please let the rider know and call us immediately.

## Return & Replacement Policy
- Eligible within **7 days of delivery** if you received a wrong, damaged, or expired item.
- For skincare and cosmetics, the product must remain **unopened and unused** with its original seal intact (unless damaged on arrival).
- For smart gadgets, please keep the box and accessories intact for warranty or replacement inspection.
- Refunds for prepaid (bKash) orders are processed within 3–5 working days after we receive and inspect the returned item.
MD,
            ],
        ];

        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => $p['slug']], $p + ['is_active' => true]);
        }
    }

    private function seedFaqs(): void
    {
        if (Faq::count() > 0) {
            return;
        }

        $faqs = [
            ['authenticity', 'Are all K-Beauty and gadget products on SafeBuy 100% original?', 'Yes. Every item is sourced directly from authorized Korean/Japanese distributors and trusted brand partners. You can also check batch codes and verification guides on our Verify Product page.'],
            ['authenticity', 'How can I verify if my Korean skincare product is genuine?', 'Check the factory seal, crisp print quality, official brand QR/hologram sticker, and batch/expiry code printed on the box and bottle. Visit our Verify Product page for a step-by-step checklist.'],
            ['orders', 'Do I need an account to place an order?', 'No! Guest checkout is available for everyone. Creating an account simply lets you save addresses, track all past orders, keep a wishlist across devices, and leave verified reviews.'],
            ['orders', 'How do I track my SafeBuy order?', 'Go to the Order Tracking page and enter your Order Number (e.g. SB-261122-0001) along with the mobile number used at checkout to see live status updates.'],
            ['orders', 'Can I request a product that is not listed on the website?', 'Yes! Use our SafeBuy Concierge page to tell us the product name, brand, or link, and our sourcing team will check availability and send you a quote.'],
            ['delivery', 'How long does delivery take inside and outside Dhaka?', 'Inside Dhaka delivery typically takes 1–2 working days. Outside Dhaka (all 64 districts of Bangladesh) takes 2–4 working days via our trusted courier partners.'],
            ['delivery', 'When do I qualify for free delivery?', 'You can see your live free-delivery progress bar right inside the cart and mini-cart. Once your subtotal reaches our free-delivery limit, shipping automatically becomes Tk 0.'],
            ['payment', 'What payment methods do you accept?', 'We accept Cash on Delivery (COD) nationwide as well as bKash. When paying via bKash, simply enter your sender number and Transaction ID at checkout.'],
            ['payment', 'Is advance payment required for outside Dhaka orders?', 'Most orders can be placed with full Cash on Delivery or bKash. For high-value or special Concierge pre-orders, our team may confirm a small delivery advance over the phone.'],
            ['returns', 'What if I receive a damaged or wrong product?', 'Please contact us within 48 hours of receiving the parcel with a quick unboxing photo or video. We will arrange a free replacement or full refund promptly.'],
            ['returns', 'Can I return an opened skincare product if it does not suit my skin?', 'For hygiene and safety reasons, opened or used cosmetics cannot be returned. If you are unsure which product fits your skin type, message us before ordering and we will gladly recommend options!'],
        ];

        foreach ($faqs as $idx => [$cat, $q, $a]) {
            Faq::create([
                'category' => $cat,
                'question' => $q,
                'answer' => $a,
                'sort_order' => $idx + 1,
                'is_active' => true,
            ]);
        }
    }

    private function seedTeam(): void
    {
        if (TeamMember::count() > 0) {
            return;
        }

        $members = [
            ['name' => 'Riaz Ahmed', 'role' => 'Founder & Operations Lead', 'bio' => 'Oversees direct supplier partnerships, authenticity standards, and nationwide fulfillment across Bangladesh.'],
            ['name' => 'Nadia Karim', 'role' => 'K-Beauty Specialist', 'bio' => 'Curates Korean & Japanese skincare routines tailored for Bangladesh’s humid climate and sensitive skin types.'],
            ['name' => 'Tanvir Hasan', 'role' => 'Smart Gadgets Curator', 'bio' => 'Tests every audio, charging, and lifestyle gadget for build quality and real-world reliability before listing.'],
            ['name' => 'Sadia Rahman', 'role' => 'Customer Care & Concierge', 'bio' => 'Leads order confirmations, after-sales care, and SafeBuy Concierge sourcing requests every day.'],
        ];

        foreach ($members as $idx => $m) {
            TeamMember::create($m + ['sort_order' => $idx + 1, 'is_active' => true]);
        }
    }

    private function seedBlog(): void
    {
        if (BlogPost::count() > 0) {
            return;
        }

        $catSkincare = BlogCategory::firstOrCreate(['slug' => 'k-beauty-guides'], ['name' => 'K-Beauty Guides', 'sort_order' => 1]);
        $catAuthenticity = BlogCategory::firstOrCreate(['slug' => 'authenticity-tips'], ['name' => 'Authenticity Tips', 'sort_order' => 2]);
        $catGadgets = BlogCategory::firstOrCreate(['slug' => 'smart-gadgets'], ['name' => 'Smart Gadgets', 'sort_order' => 3]);

        $tKorean = BlogTag::firstOrCreate(['slug' => 'korean-skincare'], ['name' => 'Korean Skincare']);
        $tOriginal = BlogTag::firstOrCreate(['slug' => 'original-check'], ['name' => 'Original Check']);
        $tRoutine = BlogTag::firstOrCreate(['slug' => 'daily-routine'], ['name' => 'Daily Routine']);
        $tGadget = BlogTag::firstOrCreate(['slug' => 'gadgets'], ['name' => 'Gadgets']);

        $authorId = User::where('role', 'owner')->value('id');
        $products = Product::active()->take(4)->get();

        $posts = [
            [
                'blog_category_id' => $catAuthenticity->id,
                'title' => 'How to Spot Fake Korean Skincare in Bangladesh: 5 Quick Checks',
                'slug' => 'how-to-spot-fake-korean-skincare-in-bangladesh',
                'excerpt' => 'Counterfeit serums and sunscreens look more convincing than ever. Here are 5 practical checks you can do in under 2 minutes before applying anything to your skin.',
                'image' => $products[0]->image ?? 'images/brand/safebuy-logo.webp',
                'body' => <<<'MD'
## Why Counterfeit Skincare Is Risky
Fake skincare products often use unverified fragrances, heavy oils, or harsh preservatives that can trigger breakouts and irritation. Knowing what to inspect protects both your skin and your wallet.

## 5 Checks Before You Use Any K-Beauty Product
- **Check the print sharpness:** Authentic Korean brands use crisp, aligned typography on both the box and bottle. Blurry fonts or spelling mistakes are a red flag.
- **Inspect the batch & expiry stamp:** Genuine products have clean embossed or laser-printed batch codes that match between the outer box and inner bottle.
- **Verify brand QR / hologram apps:** Many top brands (like COSRX, Beauty of Joseon, Anua, and Skin1004) include hidden-tag or official QR verification stickers.
- **Compare texture and scent:** Authentic Korean sunscreens and essences absorb smoothly without a strong chemical odor.
- **Buy from verified sellers:** Always check if the store guarantees direct sourcing and provides clear return support.
MD,
                'tags' => [$tKorean->id, $tOriginal->id],
            ],
            [
                'blog_category_id' => $catSkincare->id,
                'title' => 'A Simple 4-Step K-Beauty Routine for Bangladesh’s Humid Weather',
                'slug' => 'simple-4-step-k-beauty-routine-bangladesh-humid-weather',
                'excerpt' => 'You do not need 10 heavy layers in hot and humid weather. Build a lightweight, breathable Korean skincare routine with just 4 essentials.',
                'image' => $products[1]->image ?? 'images/brand/safebuy-logo.webp',
                'body' => <<<'MD'
## Keep It Light, Consistent, and Breathable
High humidity in Bangladesh means heavy creams can feel sticky and clog pores. A focused 4-step routine keeps your skin hydrated, calm, and protected all day.

## The 4 Daily Steps
1. **Gentle Low-pH Cleanser:** Removes sweat, dust, and excess oil without stripping your moisture barrier.
2. **Hydrating Toner or Essence:** Patting on a lightweight centella or snail mucin layer calms redness immediately.
3. **Targeted Serum:** Choose niacinamide for pores/oil control or vitamin C for dullness and dark spots.
4. **Broad-Spectrum Sunscreen (SPF 50+):** Non-negotiable every morning — Korean sunscreens leave zero white cast even in peak summer!
MD,
                'tags' => [$tKorean->id, $tRoutine->id],
            ],
            [
                'blog_category_id' => $catGadgets->id,
                'title' => 'Smart Desk & Everyday Gadgets Worth Upgrading in 2026',
                'slug' => 'smart-desk-everyday-gadgets-worth-upgrading',
                'excerpt' => 'From fast GaN chargers to crystal-clear earbuds, here are the practical smart gadgets that actually save time every single day.',
                'image' => $products[2]->image ?? 'images/brand/safebuy-logo.webp',
                'body' => <<<'MD'
## Practical Tech Beats Gimmicks
When choosing everyday gadgets, battery life, safe charging chips, and solid build quality matter far more than flashy specs.

## What to Look For
- **Certified Fast Charging:** Look for multi-port chargers with over-voltage and heat protection so your phone battery stays healthy.
- **Comfortable Everyday Audio:** Lightweight ANC earbuds with reliable mic quality make calls and commutes effortless.
- **Compact Portability:** Slim accessories that fit in your pocket or backpack without cable clutter.
MD,
                'tags' => [$tGadget->id],
            ],
        ];

        foreach ($posts as $idx => $data) {
            $tagIds = $data['tags'];
            unset($data['tags']);

            $post = BlogPost::create($data + [
                'author_id' => $authorId,
                'is_published' => true,
                'published_at' => now()->subDays(5 - $idx),
                'views' => 45 + ($idx * 18),
            ]);

            $post->tags()->sync($tagIds);
            if ($products->isNotEmpty()) {
                $post->products()->sync($products->take(3)->pluck('id'));
            }
        }
    }
}
