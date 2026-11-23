<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Live search for the header dropdown. */
class SearchController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['count' => 0, 'items' => []]);
        }

        $query = Product::query()->where('is_active', true)->with(['brand', 'variants']);
        foreach (preg_split('/\s+/', $q) as $word) {
            $like = '%'.$word.'%';
            $query->where(fn ($w) => $w->where('name', 'like', $like)
                ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like))
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)));
        }
        if ($cat = $request->query('cat')) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $cat));
        }

        $count = (clone $query)->count();
        $items = $query->orderBy('sort_order')->limit(6)->get()->map(fn (Product $p) => [
            'name' => $p->name,
            'brand' => $p->brand?->name,
            'url' => route('product.show', $p),
            'image' => asset($p->image),
            'price' => $p->priceLabel(),
        ]);

        return response()->json([
            'count' => $count,
            'items' => $items,
            'all' => route('search', array_filter(['q' => $q, 'cat' => $cat])),
        ]);
    }
}
