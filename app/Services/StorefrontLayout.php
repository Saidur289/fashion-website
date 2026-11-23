<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Menu;
use App\Models\Testimonial;
use App\Models\TrustBadge;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Loader for all admin-managed storefront layout data (homepage sections,
 * hero slides, banners, testimonials, header/footer menus, and trust badges).
 * Falls back cleanly when tables are empty (e.g. fresh test DB without seeders).
 *
 * Loaded once per request and kept in memory, NOT in Laravel's cache: the cache store
 * refuses to unserialize Eloquent objects (config/cache.php 'serializable_classes' => false),
 * so cached models came back broken and every storefront page returned a 500 error.
 * These are six small indexed queries, so per-request loading is cheap.
 */
class StorefrontLayout
{
    // Old cache entry from the earlier version; cleared so no broken copy is left behind.
    private const CACHE_KEY = 'safebuy.storefront.layout.v1';

    // Per-request copy lives in the service container (rebuilt for every request and every test).
    private const MEMO = 'safebuy.storefront.layout';

    public static function clearCache(): void
    {
        app()->forgetInstance(self::MEMO);
        Cache::forget(self::CACHE_KEY);
    }

    public static function all(): array
    {
        if (app()->bound(self::MEMO)) {
            return app(self::MEMO);
        }

        try {
            if (! Schema::hasTable('home_sections')) {
                return [];
            }

            $data = [
                'sections' => HomeSection::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
                'slides' => HeroSlide::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
                'banners' => Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->keyBy('slot'),
                'testimonials' => Testimonial::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
                'menus' => Menu::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('location'),
                'badges' => TrustBadge::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('location'),
            ];
            app()->instance(self::MEMO, $data);

            return $data;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function sections(): Collection
    {
        $items = self::all()['sections'] ?? collect();
        if ($items->isNotEmpty()) {
            return $items;
        }

        // Default ordered section list if not yet seeded
        $defaults = [
            ['key' => 'hero', 'name' => 'Hero Slider'],
            ['key' => 'categories', 'name' => 'Categories', 'title' => 'Beauty & Lifestyle Essentials'],
            ['key' => 'promo', 'name' => 'Offer Promo Banner'],
            ['key' => 'products_tabs', 'name' => 'Products Tabs', 'eyebrow' => 'Shop the collection', 'title' => 'Fresh & Trending'],
            ['key' => 'best_selling', 'name' => 'Best Selling', 'eyebrow' => 'Customer favourites', 'title' => 'Best Selling'],
            ['key' => 'why_shop', 'name' => 'Why Shop with SafeBuy', 'title' => 'Why Shop with SafeBuy?'],
            ['key' => 'twin_banners', 'name' => 'Twin Banners'],
            ['key' => 'budget', 'name' => 'Budget Picks', 'eyebrow' => 'Small price, real thing'],
            ['key' => 'routine', 'name' => 'Shop by Routine', 'eyebrow' => 'Build your routine', 'title' => 'Everything Your Skin & Day Needs', 'subtitle' => 'From morning sunscreen to a charged phone by night, pick a step and see what we have in stock.'],
            ['key' => 'concierge', 'name' => 'SafeBuy Concierge'],
            ['key' => 'philosophy', 'name' => 'Brand Philosophy & Compare', 'eyebrow' => 'Our brand philosophy', 'title' => 'Smart Choice, Uncompromised Trust.'],
            ['key' => 'testimonials', 'name' => 'Testimonials', 'eyebrow' => 'Testimonials', 'title' => 'What Our Clients Say'],
            ['key' => 'brands_marquee', 'name' => 'Trusted Brand Partners', 'title' => 'Trusted brand partners'],
        ];

        return collect($defaults)->map(fn ($d) => (object) ($d + ['eyebrow' => null, 'title' => null, 'subtitle' => null, 'settings' => []]));
    }

    public static function slides(): Collection
    {
        return self::all()['slides'] ?? collect();
    }

    public static function banner(string $slot): ?Banner
    {
        $banners = self::all()['banners'] ?? collect();

        return $banners->get($slot);
    }

    public static function testimonials(): Collection
    {
        return self::all()['testimonials'] ?? collect();
    }

    public static function menu(string $location): Collection
    {
        $menus = self::all()['menus'] ?? collect();

        return $menus->get($location, collect());
    }

    public static function badges(string $location): Collection
    {
        $badges = self::all()['badges'] ?? collect();

        return $badges->get($location, collect());
    }
}
