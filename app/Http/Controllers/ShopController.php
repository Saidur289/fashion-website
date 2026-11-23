<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Shop catalogue: /shop, /category/{slug}, /brand/{slug} and /search all share one filterable page.
 * Filters live in the query string, so every filtered view has its own shareable URL.
 * Fetch requests (X-Requested-With) get only the results area back, for filtering without a page reload.
 */
class ShopController extends Controller
{
    public const PER_PAGE = 12;

    public const SORTS = [
        'default' => 'Default sorting',
        'popularity' => 'Sort by popularity',
        'rating' => 'Sort by average rating',
        'latest' => 'Sort by latest',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
    ];

    public function index(Request $request)
    {
        $group = in_array($request->query('group'), [Category::GROUP_BEAUTY, Category::GROUP_GADGET], true) ? $request->query('group') : null;
        $title = match ($group) {
            Category::GROUP_BEAUTY => 'K-Beauty',
            Category::GROUP_GADGET => 'Smart Gadgets',
            default => 'Shop',
        };

        return $this->render($request, $title, [['Shop', route('shop')]], ['group' => $group]);
    }

    public function category(Request $request, Category $category)
    {
        return $this->render($request, $category->name, [['Shop', route('shop')], [$category->name, null]], ['category' => $category]);
    }

    public function brand(Request $request, Brand $brand)
    {
        return $this->render($request, $brand->name, [['Shop', route('shop')], [$brand->name, null]], ['brand' => $brand]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        return $this->render($request, $q !== '' ? 'Search: “'.$q.'”' : 'Search', [['Shop', route('shop')], ['Search', null]], ['q' => $q]);
    }

    /* ------------------------------------------------------------------ */

    private function render(Request $request, string $title, array $crumbs, array $context)
    {
        $f = $this->filters($request, $context);

        // Base = page context only (category / brand / group / search). Facets and the price range come from it.
        $base = $this->baseQuery($context, $f);
        $bounds = ['min' => (int) (clone $base)->min('price'), 'max' => (int) (clone $base)->max('price')];

        $query = $this->applyFilters(clone $base, $f);
        $this->applySort($query, $f['sort']);

        $products = $query->with(['category', 'brand', 'variants.values.attribute'])
            ->paginate($f['per_page'])
            ->withQueryString();

        $data = [
            'title' => $title,
            'crumbs' => $crumbs,
            'context' => $context,
            'f' => $f,
            'products' => $products,
            'bounds' => $bounds,
            'facets' => $this->facets($base),
            'sorts' => self::SORTS,
            'action' => $request->url(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('shop._main', $data)->render(),
                'title' => $title,
            ]);
        }

        return view('shop.index', $data);
    }

    private function filters(Request $request, array $context): array
    {
        $list = fn (string $key) => collect((array) $request->query($key, []))->filter(fn ($v) => is_string($v) && $v !== '')->values()->all();

        return [
            'cat' => $list('cat'),
            'brand' => $list('brand'),
            'color' => $list('color'),
            'size' => $list('size'),
            'tag' => $list('tag'),
            'on_sale' => $request->boolean('on_sale'),
            'in_stock' => $request->boolean('in_stock'),
            'min' => $request->filled('min') ? max(0, (int) $request->query('min')) : null,
            'max' => $request->filled('max') ? max(0, (int) $request->query('max')) : null,
            'sort' => array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'default',
            'view' => $request->query('view') === 'list' ? 'list' : 'grid',
            'per_page' => in_array((int) $request->query('per_page'), [12, 24, 36], true) ? (int) $request->query('per_page') : self::PER_PAGE,
            'q' => $context['q'] ?? null,
        ];
    }

    private function baseQuery(array $context, array $f): Builder
    {
        $query = Product::query()->where('is_active', true);

        if (! empty($context['category'])) {
            $query->where('category_id', $context['category']->id);
        }
        if (! empty($context['brand'])) {
            $query->where('brand_id', $context['brand']->id);
        }
        if (! empty($context['group'])) {
            $query->inGroup($context['group']);
        }
        if (! empty($context['q'])) {
            $words = preg_split('/\s+/', $context['q']);
            foreach ($words as $word) {
                $like = '%'.$word.'%';
                $query->where(fn (Builder $q) => $q->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', $like))
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $like)));
            }
        }
        if (! empty($context['search_cat'])) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $context['search_cat']));
        }

        return $query;
    }

    private function applyFilters(Builder $query, array $f): Builder
    {
        if ($f['cat']) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $f['cat']));
        }
        if ($f['brand']) {
            $query->whereHas('brand', fn ($q) => $q->whereIn('slug', $f['brand']));
        }
        foreach (['color', 'size'] as $attr) {
            if ($f[$attr]) {
                $query->whereHas('attributeValues', fn ($q) => $q->whereIn('attribute_values.slug', $f[$attr])
                    ->whereHas('attribute', fn ($a) => $a->where('slug', $attr)));
            }
        }
        if ($f['tag']) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('slug', $f['tag']));
        }
        if ($f['on_sale']) {
            $query->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');
        }
        if ($f['in_stock']) {
            $query->where(fn ($q) => $q
                ->where(fn ($s) => $s->doesntHave('variants')->where(fn ($n) => $n->whereNull('stock')->orWhere('stock', '>', 0)))
                ->orWhereHas('variants', fn ($v) => $v->whereNull('stock')->orWhere('stock', '>', 0)));
        }
        if ($f['min'] !== null) {
            $query->where('price', '>=', $f['min']);
        }
        if ($f['max'] !== null) {
            $query->where('price', '<=', $f['max']);
        }

        return $query;
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'popularity' => $query->orderByDesc('sold_count')->orderBy('sort_order'),
            'rating' => $query->orderByRaw('rating IS NULL, rating DESC')->orderBy('sort_order'),
            'latest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderBy('sort_order'),
        };
    }

    /** Filter options with product counts, limited to what exists on this page. */
    private function facets(Builder $base): array
    {
        $ids = (clone $base)->pluck('id');

        $categories = Category::orderBy('sort_order')
            ->withCount(['products' => fn ($q) => $q->whereIn('id', $ids)])
            ->get()->where('products_count', '>', 0);

        $brands = Brand::orderBy('sort_order')
            ->withCount(['products' => fn ($q) => $q->whereIn('id', $ids)])
            ->get()->where('products_count', '>', 0);

        $tags = Tag::orderBy('name')
            ->withCount(['products' => fn ($q) => $q->whereIn('products.id', $ids)])
            ->get()->where('products_count', '>', 0);

        $attributes = Attribute::with(['values' => fn ($q) => $q->withCount(['products' => fn ($p) => $p->whereIn('products.id', $ids)])])
            ->orderBy('sort_order')->get()
            ->mapWithKeys(fn ($a) => [$a->slug => ['attribute' => $a, 'values' => $a->values->where('products_count', '>', 0)]])
            ->filter(fn ($a) => $a['values']->isNotEmpty());

        return compact('categories', 'brands', 'tags', 'attributes');
    }

    /** Active filters as removable chips: [label, url without that filter]. */
    public static function chips(Request $request, array $f, array $facets): Collection
    {
        $chips = collect();
        $query = $request->query();
        $without = function (string $key, ?string $value = null) use ($query, $request) {
            $q = $query;
            if ($value === null) {
                unset($q[$key]);
            } else {
                $q[$key] = array_values(array_diff((array) ($q[$key] ?? []), [$value]));
            }
            unset($q['page']);

            return $request->url().($q ? '?'.http_build_query($q) : '');
        };

        $names = [
            'cat' => $facets['categories']->pluck('name', 'slug'),
            'brand' => $facets['brands']->pluck('name', 'slug'),
            'tag' => $facets['tags']->pluck('name', 'slug'),
            'color' => isset($facets['attributes']['color']) ? $facets['attributes']['color']['values']->pluck('value', 'slug') : collect(),
            'size' => isset($facets['attributes']['size']) ? $facets['attributes']['size']['values']->pluck('value', 'slug') : collect(),
        ];
        foreach ($names as $key => $map) {
            foreach ($f[$key] as $slug) {
                $chips->push([$map[$slug] ?? $slug, $without($key, $slug)]);
            }
        }
        if ($f['on_sale']) {
            $chips->push(['On sale', $without('on_sale')]);
        }
        if ($f['in_stock']) {
            $chips->push(['In stock', $without('in_stock')]);
        }
        if ($f['min'] !== null || $f['max'] !== null) {
            $q = $query;
            unset($q['min'], $q['max'], $q['page']);
            $chips->push([Product::taka($f['min'] ?? 0).' – '.Product::taka($f['max'] ?? 0), $request->url().($q ? '?'.http_build_query($q) : '')]);
        }

        return $chips;
    }
}
