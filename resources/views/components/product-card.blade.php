@props(['product', 'index' => 0])
@php
    $p = $product;
    $url = route('product.show', $p);
    $swatches = $p->isVariable()
        ? $p->variants->flatMap->values->filter(fn ($v) => $v->attribute->type === 'color')->unique('id')
        : collect();
    $sizes = $p->isVariable()
        ? $p->variants->flatMap->values->filter(fn ($v) => $v->attribute->type !== 'color')->unique('id')
        : collect();
    $catName = $p->category?->name ?? 'Essentials';
    $catSlug = strtolower((string) ($p->category?->slug ?? $catName));
    $groupLabel = ($p->category?->group === \App\Models\Category::GROUP_GADGET) ? 'Gadgets' : 'K-Beauty';
    $catIcon = match (true) {
        str_contains($catSlug, 'sun') => 'sun',
        str_contains($catSlug, 'moist') || str_contains($catSlug, 'cream') || str_contains($catSlug, 'lotion') => 'droplet',
        str_contains($catSlug, 'watch') => 'watch',
        str_contains($catSlug, 'ear') || str_contains($catSlug, 'audio') || str_contains($catSlug, 'head') => 'headphones',
        str_contains($catSlug, 'power') || str_contains($catSlug, 'charg') => 'zap',
        default => 'sparkles',
    };
    $displayRating = $p->rating ? (float) $p->rating : 4.0;
@endphp
{{-- SafeBuyStore.com matched product card: 1:1 rounded-top image, red discount pill, hover quick actions + slide-up blue->orange CTA --}}
<li class="card" style="--cd: {{ number_format($index * 0.07, 2) }}s" data-product='@json($p->cardData())'>
    <div class="pimg">
        <a href="{{ $url }}" class="pimg-link" aria-label="{{ $p->name }}" data-view>
            <img class="main" src="{{ asset($p->image) }}" alt="{{ $p->name }}" loading="lazy" decoding="async" width="600" height="600">
            @if ($p->image_hover)
                <img class="alt" src="{{ asset($p->image_hover) }}" alt="" loading="lazy" decoding="async" width="600" height="600">
            @endif
        </a>
        <div class="tags">
            @if ($p->discount_percent)<span class="tag off">-{{ $p->discount_percent }}%Off</span>@endif
            @if ($p->is_featured && ! $p->discount_percent)<span class="tag feat">Featured</span>@endif
            @unless ($p->inStock())<span class="tag out">Sold out</span>@endunless
        </div>
        <div class="acts">
            <button class="act" type="button" aria-label="Add to wishlist" aria-pressed="false" data-wish="{{ $p->id }}"><x-icon name="heart" size="16" /></button>
            <button class="act" type="button" aria-label="Compare" data-compare-add="{{ $p->id }}"><x-icon name="compare" size="16" /></button>
            <button class="act" type="button" aria-label="Quick view" data-quick="{{ route('product.quick', $p) }}"><x-icon name="eye" size="16" /></button>
        </div>
        @if ($p->isVariable())
            <a class="addc" href="{{ $url }}" data-view><x-icon name="arrow-right" size="15" /><span>Select options</span></a>
        @elseif ($p->inStock())
            <button class="addc" type="button" data-add="{{ $p->id }}"><x-icon name="bag" size="15" /><span>Add to cart</span></button>
        @else
            <a class="addc muted" href="{{ $url }}" data-view><span>Read more</span></a>
        @endif
    </div>
    <div class="pbody">
        @if ($p->isVariable())
            <a class="addc-mob" href="{{ $url }}" data-view><x-icon name="arrow-right" size="14" /><span>Select options</span></a>
        @elseif ($p->inStock())
            <button class="addc-mob" type="button" data-add="{{ $p->id }}"><x-icon name="bag" size="14" /><span>Add to cart</span></button>
        @else
            <a class="addc-mob muted" href="{{ $url }}" data-view><span>Read more</span></a>
        @endif
        <span class="pcat-pill"><x-icon :name="$catIcon" size="12" stroke="2" /> {{ $catName }}</span>
        <div class="pmeta-row">
            <span class="pgroup">{{ $groupLabel }}</span>
            <div class="stars" aria-label="{{ $p->rating ? 'Rated '.$p->rating.' out of 5' : 'No reviews yet' }}">
                <span class="srow" style="--r: {{ $displayRating * 20 }}%" aria-hidden="true">★★★★★</span>
                <span class="scount">{{ $p->reviews_count }}</span>
            </div>
        </div>
        <h3 class="pname"><a href="{{ $url }}" data-view>{{ $p->name }}</a></h3>
        <div class="price">
            <span class="now">{{ str_replace('Tk ', 'Tk', $p->priceLabel()) }}</span>
            @if ($p->old_price && ! $p->isVariable())
                <span class="was">{{ str_replace('Tk ', 'Tk', \App\Models\Product::taka($p->old_price)) }}</span>
            @endif
        </div>
        @if ($swatches->isNotEmpty())
            <div class="sw" aria-label="Colours">
                @foreach ($swatches as $s)<span class="swd" style="--c: {{ $s->color }}" title="{{ $s->value }}"></span>@endforeach
            </div>
        @elseif ($sizes->isNotEmpty())
            <div class="sizes" aria-label="Sizes">
                @foreach ($sizes as $s)<span>{{ $s->value }}</span>@endforeach
            </div>
        @endif
        <p class="pdesc">{{ \Illuminate\Support\Str::limit($p->summary, 150) }}</p>
    </div>
</li>
