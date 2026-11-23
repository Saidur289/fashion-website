<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const WITH = ['category', 'brand', 'tags', 'images', 'variants.values.attribute', 'attributeValues'];

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load(self::WITH);
        $product->load(['boughtTogether' => fn ($q) => $q->where('is_active', true)->with(['brand', 'variants'])]);

        // Previous / next product in shop order (wraps around).
        $ordered = Product::where('is_active', true)->orderBy('sort_order')->get(['id', 'slug', 'name', 'image', 'sort_order']);
        $i = $ordered->search(fn ($p) => $p->id === $product->id);
        $prev = $ordered[($i - 1 + $ordered->count()) % $ordered->count()];
        $next = $ordered[($i + 1) % $ordered->count()];

        // Related: same category first, then the same product group.
        $related = Product::active()->with(['category', 'brand', 'variants.values.attribute'])
            ->where('id', '!=', $product->id)
            ->whereHas('category', fn ($q) => $q->where('group', $product->category->group))
            ->get()
            ->sortBy(fn ($p) => $p->category_id === $product->category_id ? 0 : 1)
            ->take(8)->values();

        // Reviews: approved ones for everybody; the form only for buyers who received this product.
        $reviews = $product->reviews()->with(['user', 'images'])->get();
        $user = auth()->user();
        $reviewState = match (true) {
            ! $user => 'guest',
            \App\Models\Review::where('product_id', $product->id)->where('user_id', $user->id)->exists() => 'done',
            (bool) $user->deliveredOrderWith($product->id) => 'can',
            default => 'not_buyer',
        };

        return view('product.show', compact('product', 'prev', 'next', 'related', 'reviews', 'reviewState'));
    }

    /** Quick View popup content. */
    public function quick(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load(self::WITH);

        return view('product._quick', compact('product'));
    }
}
