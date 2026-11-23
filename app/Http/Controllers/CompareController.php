<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Compare: the list of product ids lives in the browser (like the cart for now) and is sent as ?ids=1,2,3.
 * Fetch requests get just the table for the popup; a normal visit gets the full /compare page.
 */
class CompareController extends Controller
{
    public const MAX = 4;

    public function index(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)->filter()->unique()->take(self::MAX)->values();

        $products = Product::where('is_active', true)->whereIn('id', $ids)
            ->with(['brand', 'category', 'variants.values.attribute', 'attributeValues'])
            ->get()
            ->sortBy(fn ($p) => $ids->search($p->id))->values();

        if ($request->ajax()) {
            return view('compare._table', compact('products'));
        }

        return view('compare.index', compact('products'));
    }
}
