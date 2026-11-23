<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Wishlist. Logged-in customers: saved in the database. Guests: kept in the browser and sent as ?ids=
 * (like Compare); when a guest logs in, the browser list is merged into the account with sync().
 */
class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        if ($user = $request->user()) {
            $rows = $user->wishlist()->with(['product.brand', 'product.category', 'product.variants.values.attribute', 'variant.values'])->get()
                ->filter(fn ($w) => $w->product && $w->product->is_active);
            $items = $rows->map(fn ($w) => ['product' => $w->product, 'variant' => $w->variant])->values();
        } else {
            $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($i) => (int) $i)->filter()->take(50);
            $items = Product::where('is_active', true)->whereIn('id', $ids)
                ->with(['brand', 'category', 'variants.values.attribute'])->get()
                ->sortBy(fn ($p) => $ids->search($p->id))
                ->map(fn ($p) => ['product' => $p, 'variant' => null])->values();
        }

        return view('wishlist.index', ['items' => $items, 'guest' => ! $request->user()]);
    }

    /** Add or remove one product (logged-in only; guests toggle in the browser). */
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate(['product_id' => ['required', 'integer', 'exists:products,id'], 'variant_id' => ['nullable', 'integer']]);
        $user = $request->user();
        $row = $user->wishlist()->where('product_id', $data['product_id'])->first();

        if ($row) {
            $row->delete();
            $on = false;
        } else {
            $variant = isset($data['variant_id'])
                ? Product::find($data['product_id'])->variants()->whereKey($data['variant_id'])->value('id')
                : null;
            $user->wishlist()->create(['product_id' => $data['product_id'], 'product_variant_id' => $variant]);
            $on = true;
        }

        return response()->json(['on' => $on, 'ids' => $this->ids($request)]);
    }

    /** Merge the guest's browser wishlist into the account after login. */
    public function sync(Request $request): JsonResponse
    {
        $ids = collect((array) $request->input('ids'))->map(fn ($i) => (int) $i)->filter()->unique()->take(50);
        $valid = Product::whereIn('id', $ids)->pluck('id');
        foreach ($valid as $id) {
            Wishlist::firstOrCreate(['user_id' => $request->user()->id, 'product_id' => $id]);
        }

        return response()->json(['ids' => $this->ids($request)]);
    }

    private function ids(Request $request): array
    {
        return $request->user()->wishlist()->pluck('product_id')->map(fn ($i) => (string) $i)->all();
    }
}
