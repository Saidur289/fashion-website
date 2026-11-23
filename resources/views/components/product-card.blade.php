@props(['product', 'index' => 0])
@php
    $p = $product;
    $url = route('product.show', $p);
    // Card swatches: colour values of a variable product (none of the current products has colour variants yet).
    $swatches = $p->isVariable()
        ? $p->variants->flatMap->values->filter(fn ($v) => $v->attribute->type === 'color')->unique('id')
        : collect();
    $sizes = $p->isVariable()
        ? $p->variants->flatMap->values->filter(fn ($v) => $v->attribute->type !== 'color')->unique('id')
        : collect();
@endphp
{{-- Hara-style card: hover image swap, 3 floating actions, bottom CTA. --}}
<li class="card" style="--cd: {{ number_format($index * 0.09, 2) }}s" data-product='@json($p->cardData())'>
    <div class="pimg">
        <a href="{{ $url }}" class="pimg-link" aria-label="{{ $p->name }}" data-view>
            <img class="main" src="{{ asset($p->image) }}" alt="{{ $p->name }}" loading="lazy" decoding="async" width="600" height="600">
            @if ($p->image_hover)
                <img class="alt" src="{{ asset($p->image_hover) }}" alt="" loading="lazy" decoding="async" width="600" height="600">
            @endif
        </a>
        <div class="tags">
            @if ($p->is_featured)<span class="tag feat">Featured</span>@endif
            @if ($p->discount_percent)<span class="tag off">-{{ $p->discount_percent }}%</span>@endif
            @unless ($p->inStock())<span class="tag out">Sold out</span>@endunless
        </div>
        <div class="acts">
            <button class="act" type="button" aria-label="Add to wishlist" aria-pressed="false" data-wish="{{ $p->id }}"><x-icon name="heart" size="18" /></button>
            <button class="act" type="button" aria-label="Compare" data-compare-add="{{ $p->id }}"><x-icon name="compare" size="18" /></button>
            <button class="act" type="button" aria-label="Quick view" data-quick="{{ route('product.quick', $p) }}"><x-icon name="eye" size="18" /></button>
        </div>
        @if ($p->isVariable())
            <a class="addc" href="{{ $url }}" data-view><x-icon name="arrow-right" size="16" /><span>Select options</span></a>
        @elseif ($p->inStock())
            <button class="addc" type="button" data-add="{{ $p->id }}"><x-icon name="bag" size="16" /><span>Add to cart</span></button>
        @else
            <a class="addc muted" href="{{ $url }}" data-view><span>Read more</span></a>
        @endif
    </div>
    <div class="pbody">
        @if ($p->brand)
            <a class="pbrand" href="{{ route('brand.show', $p->brand) }}">{{ $p->brand->name }}</a>
        @else
            <span class="pbrand">{{ $p->category->name }}</span>
        @endif
        <h3 class="pname"><a href="{{ $url }}" data-view>{{ $p->name }}</a></h3>
        <div class="stars" aria-label="{{ $p->rating ? 'Rated '.$p->rating.' out of 5' : 'No reviews yet' }}">
            <span class="srow" style="--r: {{ ($p->rating ?? 0) * 20 }}%" aria-hidden="true">★★★★★</span>
            <span class="scount">({{ $p->reviews_count }})</span>
        </div>
        <div class="price">
            <span class="now">{{ $p->priceLabel() }}</span>
            @if ($p->old_price && ! $p->isVariable())
                <span class="was">{{ \App\Models\Product::taka($p->old_price) }}</span>
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
