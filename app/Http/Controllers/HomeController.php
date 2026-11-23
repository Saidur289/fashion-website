<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\StorefrontLayout;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $base = Product::query()->active()->with(['category', 'brand', 'variants.values.attribute']);

        $tabs = [
            ['key' => 'new', 'label' => 'New Arrival', 'products' => (clone $base)->where('is_new_arrival', true)->get()],
            ['key' => 'beauty', 'label' => 'Trending K-Beauty', 'products' => (clone $base)->where('is_trending', true)->inGroup(Category::GROUP_BEAUTY)->get()],
            ['key' => 'gadgets', 'label' => 'Smart Gadgets', 'products' => (clone $base)->where('is_trending', true)->inGroup(Category::GROUP_GADGET)->get()],
        ];

        $maxDiscount = (clone $base)->where('is_new_arrival', true)->get()->max('discount_percent');
        $budgetLimit = config('safebuy.budget_limit');

        // Categories with their product count and lowest price ("Starts from Tk X").
        $categories = Category::orderBy('sort_order')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->withMin(['products' => fn ($q) => $q->where('is_active', true)], 'price')
            ->get();

        $brands = Brand::whereNotNull('logo')->where('logo', '!=', '')->orderBy('sort_order')->get();

        return view('home', [
            'sections' => StorefrontLayout::sections(),
            'heroSlides' => StorefrontLayout::slides(),
            'promoBanner' => StorefrontLayout::banner('promo'),
            'twinLeftBanner' => StorefrontLayout::banner('twin_left'),
            'twinRightBanner' => StorefrontLayout::banner('twin_right'),
            'conciergeBanner' => StorefrontLayout::banner('concierge'),
            'whyBadges' => StorefrontLayout::badges('home_why'),
            'testimonials' => StorefrontLayout::testimonials(),
            'partnerBrands' => $brands,
            'categories' => $categories,
            'tabs' => $tabs,
            'maxDiscount' => $maxDiscount,
            'bestSellers' => (clone $base)->where('is_best_seller', true)->get(),
            'budgetLimit' => $budgetLimit,
            'budgetProducts' => (clone $base)->where('price', '<', $budgetLimit)->reorder('price')->get(),
        ]);
    }
}
