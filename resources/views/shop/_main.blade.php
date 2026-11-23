@php
    use App\Http\Controllers\ShopController;
    use App\Models\Product;
    $chips = ShopController::chips(request(), $f, $facets);
    $lo = $f['min'] ?? $bounds['min'];
    $hi = $f['max'] ?? $bounds['max'];
    $fixedCat = ! empty($context['category']);
    $fixedBrand = ! empty($context['brand']);
@endphp
<div class="shop-grid">
    {{-- ============ FILTERS ============ --}}
    <aside class="filters" id="filters" aria-label="Product filters">
        <div class="filters-top">
            <b>Filter products</b>
            <button class="ibtn" type="button" aria-label="Close filters" data-filter-close><x-icon name="close" size="20" /></button>
        </div>
        <form class="ffm" action="{{ $action }}" method="get" data-filter-form>
            @if ($f['q'])<input type="hidden" name="q" value="{{ $f['q'] }}">@endif
            @if (! empty($context['group']))<input type="hidden" name="group" value="{{ $context['group'] }}">@endif
            <input type="hidden" name="sort" value="{{ $f['sort'] }}">
            <input type="hidden" name="view" value="{{ $f['view'] }}">

            @if (! $fixedCat && $facets['categories']->count() > 1)
                <fieldset class="fbox">
                    <legend>Categories</legend>
                    @foreach ($facets['categories'] as $c)
                        <label class="chk"><input type="checkbox" name="cat[]" value="{{ $c->slug }}" @checked(in_array($c->slug, $f['cat']))><span>{{ $c->name }}</span><em>{{ $c->products_count }}</em></label>
                    @endforeach
                </fieldset>
            @endif

            <fieldset class="fbox">
                <legend>Status</legend>
                <label class="tgl"><input type="checkbox" name="on_sale" value="1" @checked($f['on_sale'])><span>On sale</span></label>
                <label class="tgl"><input type="checkbox" name="in_stock" value="1" @checked($f['in_stock'])><span>In stock</span></label>
            </fieldset>

            @if ($bounds['max'] > $bounds['min'])
                <fieldset class="fbox" data-price data-min="{{ $bounds['min'] }}" data-max="{{ $bounds['max'] }}">
                    <legend>Price</legend>
                    <div class="range2">
                        <div class="rtrack"><i data-price-fill></i></div>
                        <label class="vh" for="pmin">Minimum price</label>
                        <input id="pmin" type="range" min="{{ $bounds['min'] }}" max="{{ $bounds['max'] }}" step="10" value="{{ $lo }}" data-price-lo>
                        <label class="vh" for="pmax">Maximum price</label>
                        <input id="pmax" type="range" min="{{ $bounds['min'] }}" max="{{ $bounds['max'] }}" step="10" value="{{ $hi }}" data-price-hi>
                    </div>
                    <input type="hidden" name="min" value="{{ $f['min'] }}" data-price-min>
                    <input type="hidden" name="max" value="{{ $f['max'] }}" data-price-max>
                    <div class="prow">
                        <span>Price: <b data-price-text>{{ Product::taka($lo) }} – {{ Product::taka($hi) }}</b></span>
                        <button class="btn sm" type="button" data-price-apply>Filter</button>
                    </div>
                </fieldset>
            @endif

            @foreach ($facets['attributes'] as $slug => $a)
                <fieldset class="fbox">
                    <legend>{{ $a['attribute']->name }}</legend>
                    <div @class(['swlist' => $a['attribute']->type === 'color', 'pills' => $a['attribute']->type !== 'color'])>
                        @foreach ($a['values'] as $v)
                            <label @class(['swopt' => $a['attribute']->type === 'color', 'pill' => $a['attribute']->type !== 'color'])>
                                <input type="checkbox" name="{{ $slug }}[]" value="{{ $v->slug }}" @checked(in_array($v->slug, $f[$slug] ?? []))>
                                @if ($a['attribute']->type === 'color')<i style="--c: {{ $v->color }}"></i>@endif
                                <span>{{ $v->value }}</span><em>{{ $v->products_count }}</em>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            @if (! $fixedBrand && $facets['brands']->count() > 1)
                <fieldset class="fbox">
                    <legend>Brands</legend>
                    @foreach ($facets['brands'] as $b)
                        <label class="chk"><input type="checkbox" name="brand[]" value="{{ $b->slug }}" @checked(in_array($b->slug, $f['brand']))><span>{{ $b->name }}</span><em>{{ $b->products_count }}</em></label>
                    @endforeach
                </fieldset>
            @endif

            @if ($facets['tags']->count() > 1)
                <fieldset class="fbox">
                    <legend>Product tags</legend>
                    <div class="pills">
                        @foreach ($facets['tags'] as $t)
                            <label class="pill"><input type="checkbox" name="tag[]" value="{{ $t->slug }}" @checked(in_array($t->slug, $f['tag']))><span>{{ $t->name }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <noscript><button class="btn" type="submit">Apply filters</button></noscript>
            <a class="reset" href="{{ $action }}{{ $f['q'] ? '?q='.urlencode($f['q']) : (! empty($context['group']) ? '?group='.$context['group'] : '') }}" data-filter-link>
                <x-icon name="close" size="14" /> Reset all filters
            </a>
        </form>
    </aside>

    {{-- ============ RESULTS ============ --}}
    <section class="results" aria-label="Products">
        <div class="toolbar">
            <button class="btn sm ghost fopen" type="button" data-filter-open aria-controls="filters"><x-icon name="menu" size="16" /> Filter</button>
            <p class="rcount" aria-live="polite">
                @if ($products->total())
                    Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }}
                @else
                    No products found
                @endif
            </p>
            <div class="tools">
                <div class="views" role="group" aria-label="Layout">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid', 'page' => null]) }}" @class(['vbtn', 'on' => $f['view'] === 'grid']) aria-label="Grid view" data-filter-link><x-icon name="grid" size="18" /></a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list', 'page' => null]) }}" @class(['vbtn', 'on' => $f['view'] === 'list']) aria-label="List view" data-filter-link><x-icon name="menu" size="18" /></a>
                </div>
                <label class="vh" for="sort">Sort products</label>
                <span class="sel">
                    <select id="sort" data-sort>
                        @foreach ($sorts as $key => $label)
                            <option value="{{ request()->fullUrlWithQuery(['sort' => $key, 'page' => null]) }}" @selected($f['sort'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" size="14" />
                </span>
            </div>
        </div>

        @if ($chips->isNotEmpty())
            <div class="chips">
                @foreach ($chips as [$label, $href])
                    <a class="fchip" href="{{ $href }}" data-filter-link aria-label="Remove filter {{ $label }}">{{ $label }} <x-icon name="close" size="12" /></a>
                @endforeach
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="empty">
                <x-icon name="search" size="36" />
                <h2>Nothing matches these filters</h2>
                <p>Try removing a filter or searching for something else.</p>
                <a class="btn" href="{{ route('shop') }}" data-filter-link>See all products</a>
            </div>
        @else
            <ul @class(['pgrid', 'list' => $f['view'] === 'list']) data-stagger="0.05">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :index="$loop->index" />
                    @if ($loop->iteration === 6 && $products->count() > 6 && (! isset($shopPromoBanner) || $shopPromoBanner !== null))
                        @php
                            $spTitle = $shopPromoBanner?->title ?: '100% secure delivery across Bangladesh';
                            $spSub = $shopPromoBanner?->subtitle ?: 'Every order is barcode-verified before it leaves us. Pay cash when it arrives.';
                            $spCta = $shopPromoBanner?->cta_label ?: "Can't find it? Ask us";
                            $spUrl = $shopPromoBanner?->cta_url ? url($shopPromoBanner->cta_url) : route('concierge');
                        @endphp
                        <li class="inpromo">
                            <span class="ipi"><x-icon name="truck" size="26" /></span>
                            <div>
                                <b>{{ $spTitle }}</b>
                                <p>{{ $spSub }}</p>
                            </div>
                            <a class="btn orange sm" href="{{ $spUrl }}">{{ $spCta }} <x-icon name="arrow-right" size="14" /></a>
                        </li>
                    @endif
                @endforeach
            </ul>
            @if ($products->hasPages())
                <nav class="pager" aria-label="Pages">
                    @if ($products->onFirstPage())<span class="pg off"><x-icon name="arrow-left" size="16" /></span>@else<a class="pg" href="{{ $products->previousPageUrl() }}" data-filter-link aria-label="Previous page"><x-icon name="arrow-left" size="16" /></a>@endif
                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $n => $u)
                        @if ($n === $products->currentPage())<span class="pg on" aria-current="page">{{ $n }}</span>@else<a class="pg" href="{{ $u }}" data-filter-link>{{ $n }}</a>@endif
                    @endforeach
                    @if ($products->hasMorePages())<a class="pg" href="{{ $products->nextPageUrl() }}" data-filter-link aria-label="Next page"><x-icon name="arrow-right" size="16" /></a>@else<span class="pg off"><x-icon name="arrow-right" size="16" /></span>@endif
                </nav>
            @endif
        @endif
    </section>
</div>
<button class="fscrim" type="button" aria-label="Close filters" data-filter-close tabindex="-1"></button>
