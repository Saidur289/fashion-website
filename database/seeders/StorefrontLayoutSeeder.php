<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Testimonial;
use App\Models\TrustBadge;
use App\Services\StorefrontLayout;
use Illuminate\Database\Seeder;

class StorefrontLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSections();
        $this->seedHeroSlides();
        $this->seedBanners();
        $this->seedTestimonials();
        $this->seedMenus();
        $this->seedTrustBadges();

        StorefrontLayout::clearCache();
    }

    private function seedSections(): void
    {
        $sections = [
            ['key' => 'hero', 'name' => 'Hero Slider', 'eyebrow' => null, 'title' => null, 'subtitle' => null],
            ['key' => 'categories', 'name' => 'Categories Grid', 'eyebrow' => null, 'title' => 'Beauty & Lifestyle Essentials', 'subtitle' => null],
            ['key' => 'promo', 'name' => 'Offer Promo Banner', 'eyebrow' => null, 'title' => null, 'subtitle' => null],
            ['key' => 'products_tabs', 'name' => 'Fresh & Trending Tabs', 'eyebrow' => 'Shop the collection', 'title' => 'Fresh & Trending', 'subtitle' => null],
            ['key' => 'best_selling', 'name' => 'Best Selling Products', 'eyebrow' => 'Customer favourites', 'title' => 'Best Selling', 'subtitle' => null],
            ['key' => 'why_shop', 'name' => 'Why Shop with SafeBuy', 'eyebrow' => null, 'title' => 'Why Shop with SafeBuy?', 'subtitle' => null],
            ['key' => 'twin_banners', 'name' => 'Twin Category Banners', 'eyebrow' => null, 'title' => null, 'subtitle' => null],
            ['key' => 'budget', 'name' => 'Budget Picks (Under Tk X)', 'eyebrow' => 'Small price, real thing', 'title' => null, 'subtitle' => null],
            ['key' => 'routine', 'name' => 'Shop by Routine (Progress Bars)', 'eyebrow' => 'Build your routine', 'title' => 'Everything Your Skin & Day Needs', 'subtitle' => 'From morning sunscreen to a charged phone by night, pick a step and see what we have in stock.'],
            ['key' => 'concierge', 'name' => 'SafeBuy Concierge Showcase', 'eyebrow' => null, 'title' => null, 'subtitle' => null],
            ['key' => 'philosophy', 'name' => 'Brand Philosophy & Counterfeit Compare', 'eyebrow' => 'Our brand philosophy', 'title' => 'Smart Choice, Uncompromised Trust.', 'subtitle' => null],
            ['key' => 'testimonials', 'name' => 'Customer Testimonials', 'eyebrow' => 'Testimonials', 'title' => 'What Our Clients Say', 'subtitle' => null],
            ['key' => 'brands_marquee', 'name' => 'Trusted Brand Partners Marquee', 'eyebrow' => null, 'title' => 'Trusted brand partners', 'subtitle' => null],
        ];

        foreach ($sections as $idx => $s) {
            HomeSection::firstOrCreate(
                ['key' => $s['key']],
                $s + ['sort_order' => $idx + 1, 'is_active' => true]
            );
        }
    }

    private function seedHeroSlides(): void
    {
        if (HeroSlide::count() > 0) {
            return;
        }

        $slides = [
            [
                'eyebrow' => 'Original Korean Care',
                'title' => "Get The Perfectly\nHydrated Skin",
                'description' => '100% authentic Korean & Japanese skincare, delivered across Bangladesh.',
                'cta_label' => 'Shop now',
                'cta_url' => '/shop',
                'bg_image' => null,
                'image_primary' => 'images/hero/hara/s1-model.webp',
                'image_secondary' => 'images/hero/hara/s1-products.webp',
                'layout_style' => 'split',
                'accent_color' => '#F5E9D9',
                'sort_order' => 1,
            ],
            [
                'eyebrow' => 'Body & Hair',
                'title' => "Clean Matte Liquid\nFoundation",
                'description' => 'Lightweight, breathable coverage that lasts all day — verified original.',
                'cta_label' => 'Shop now',
                'cta_url' => '/shop?group=beauty',
                'bg_image' => null,
                'image_primary' => 'images/hero/hara/s2-arch-product.webp',
                'image_secondary' => 'images/hero/hara/s2-leaves.webp',
                'layout_style' => 'arch',
                'accent_color' => '#ECECEC',
                'sort_order' => 2,
            ],
            [
                'eyebrow' => 'Spring Day',
                'title' => "Changing The Ideal of\nBeauty",
                'description' => 'Serums, day creams and night creams that protect, moisturise and nourish your skin — sourced straight from trusted brands.',
                'cta_label' => 'Shop now',
                'cta_url' => '/shop?group=beauty',
                'bg_image' => null,
                'image_primary' => 'images/hero/hara/s3-product.webp',
                'image_secondary' => null,
                'layout_style' => 'rings',
                'accent_color' => '#ECECEC',
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $s) {
            HeroSlide::create($s + ['is_active' => true]);
        }
    }

    private function seedBanners(): void
    {
        $banners = [
            [
                'slot' => 'promo',
                'eyebrow' => 'Smart Choice, Safe Shopping',
                'title' => 'Where Premium K-Beauty Meets Next-Gen Gadgets.',
                'subtitle' => 'Get the best of both worlds. Grab 100% original Korean skincare and premium tech gadgets at unbeatable prices.',
                'cta_label' => 'Shop now',
                'cta_url' => '/shop?on_sale=1',
                'image' => 'images/banners/offer-right.png',
                'theme' => 'light',
                'sort_order' => 1,
            ],
            [
                'slot' => 'twin_left',
                'eyebrow' => 'Korean Beauty',
                'title' => '100% Original K-Beauty Essentials',
                'subtitle' => 'Your daily dose of authenticity.',
                'cta_label' => 'Shop Beauty',
                'cta_url' => '/shop?group=beauty',
                'image' => 'images/banners/twin-left.png',
                'theme' => 'light',
                'sort_order' => 2,
            ],
            [
                'slot' => 'twin_right',
                'eyebrow' => 'Smart Lifestyle',
                'title' => 'Premium Lifestyle & Smart Gadgets',
                'subtitle' => 'Upgrade to smarter living.',
                'cta_label' => 'Shop Gadgets',
                'cta_url' => '/shop?group=gadget',
                'image' => 'images/banners/twin-right.png',
                'theme' => 'dark',
                'sort_order' => 3,
            ],
            [
                'slot' => 'concierge',
                'eyebrow' => 'Premium Product Sourcing Service',
                'title' => 'Hunting for a Specific K-Beauty or J-Beauty Secret?',
                'subtitle' => "Can't find your favorite authentic Korean or Japanese skincare product anywhere in Bangladesh? Stop scrolling and let our global sourcing team fly it directly to you.",
                'cta_label' => 'Launch concierge request',
                'cta_url' => '/concierge',
                'image' => 'images/banners/concierge-bg.png',
                'theme' => 'dark',
                'sort_order' => 4,
            ],
            [
                'slot' => 'shop_promo',
                'eyebrow' => null,
                'title' => '100% secure delivery across Bangladesh',
                'subtitle' => 'Every order is barcode-verified before it leaves us. Pay cash when it arrives.',
                'cta_label' => "Can't find it? Ask us",
                'cta_url' => '/concierge',
                'image' => null,
                'theme' => 'light',
                'sort_order' => 5,
            ],
        ];

        foreach ($banners as $b) {
            Banner::firstOrCreate(['slot' => $b['slot']], $b + ['is_active' => true]);
        }
    }

    private function seedTestimonials(): void
    {
        if (Testimonial::count() > 0) {
            return;
        }

        Testimonial::create([
            'name' => 'Anika Rahman',
            'location' => 'Dhaka — K-Beauty Customer',
            'quote' => 'With the fake Korean cosmetics in the market, I was afraid to buy anything online. But I am 100% satisfied with the sunscreen from SafeBuy. I got the original product by scanning the barcode. Thank you SafeBuy!',
            'photo' => 'images/testimonials/anika.webp',
            'rating' => 5,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Testimonial::create([
            'name' => 'Tanvir Ahmed',
            'location' => 'Chittagong — Smart Lifestyle Buyer',
            'quote' => 'The quality of their lifestyle gadgets is truly premium. Delivery was very fast and the convenience of paying after receiving the product was great.',
            'photo' => 'images/testimonials/tanvir.webp',
            'rating' => 5,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    private function seedMenus(): void
    {
        if (Menu::count() > 0) {
            return;
        }

        $header = [
            ['Home', '/'],
            ['Shop', '/shop'],
            ['K-Beauty', '/shop?group=beauty'],
            ['Smart Gadgets', '/shop?group=gadget'],
            ['Concierge', '/concierge'],
            ['Blog', '/blog'],
            ['About', '/about'],
            ['FAQ', '/faq'],
            ['Contact', '/contact'],
        ];
        foreach ($header as $i => [$label, $url]) {
            Menu::create(['location' => 'header', 'label' => $label, 'url' => $url, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $quick = [
            ['How to Verify Product', '/verify-product'],
            ['SafeBuy Concierge', '/concierge'],
            ['Shipping & Returns', '/shipping-returns'],
            ['Privacy Policy', '/privacy-policy'],
            ['Terms of Use', '/terms'],
            ['Wishlist', '/wishlist'],
            ['Compare', '/compare'],
            ['Order Tracking', '/order-tracking'],
        ];
        foreach ($quick as $i => [$label, $url]) {
            Menu::create(['location' => 'footer_quick', 'label' => $label, 'url' => $url, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $bottom = [
            ['About Us', '/about'],
            ['Our Team', '/our-team'],
            ['FAQs', '/faq'],
            ['Blog', '/blog'],
            ['Contact', '/contact'],
        ];
        foreach ($bottom as $i => [$label, $url]) {
            Menu::create(['location' => 'footer_bottom', 'label' => $label, 'url' => $url, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }

    private function seedTrustBadges(): void
    {
        if (TrustBadge::count() > 0) {
            return;
        }

        $productBadges = [
            ['truck', 'Delivery all over Bangladesh', null],
            ['shield', '100% authentic, barcode verified', null],
            ['card', 'Cash on delivery & bKash', null],
        ];
        foreach ($productBadges as $i => [$icon, $title, $sub]) {
            TrustBadge::create(['location' => 'product', 'icon' => $icon, 'title' => $title, 'subtitle' => $sub, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $whyBadges = [
            ['shield', '100% Authentic', 'Only genuine products.'],
            ['globe', 'Global Sourcing', 'Direct from Seoul & Tokyo.'],
            ['barcode', 'Safe & Secure', 'Barcode verified & trackable.'],
            ['headset', 'Concierge Support', 'Request to doorstep.'],
        ];
        foreach ($whyBadges as $i => [$icon, $title, $sub]) {
            TrustBadge::create(['location' => 'home_why', 'icon' => $icon, 'title' => $title, 'subtitle' => $sub, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
