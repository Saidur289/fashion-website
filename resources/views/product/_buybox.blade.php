{{--
    Buy box shared by the product page and the Quick View popup.
    $product, $quick (bool). safebuy.js reads data-variants to switch price, stock, SKU and image.
--}}
@php
    use App\Models\Product;
    $p = $product;
    $stock = $p->totalStock();
    $options = $p->variantOptions();
@endphp
<div class="buy" data-buy data-product='@json($p->cardData())' data-variants='@json($p->variantData())'>
    {{-- Hara order: price, name, rating + sold, short feature points, options, chosen price, stock. --}}
    @if ($p->isVariable())
        <div class="bprice top"><span class="now">{{ $p->priceLabel() }}</span></div>
    @else
        <div class="bprice top">
            <span class="now" data-b-price>{{ $p->priceLabel() }}</span>
            <span class="was" data-b-old>@if ($p->old_price){{ Product::taka($p->old_price) }}@endif</span>
            @if ($p->discount_percent)<span class="tag off">-{{ $p->discount_percent }}%</span>@endif
        </div>
    @endif

    @if ($quick ?? false)
        <h2 class="bname"><a href="{{ route('product.show', $p) }}">{{ $p->name }}</a></h2>
    @else
        <h1 class="bname">{{ $p->name }}</h1>
    @endif

    <div class="bmeta">
        <span class="stars">
            <span class="srow" style="--r: {{ ($p->rating ?? 0) * 20 }}%" aria-hidden="true">★★★★★</span>
            <a href="{{ route('product.show', $p) }}#reviews" class="scount" data-tab-link="reviews">({{ $p->reviews_count }} customer {{ \Illuminate\Support\Str::plural('review', $p->reviews_count) }})</a>
        </span>
        @if ($p->sold_count > 0)<span class="sold">Sold: {{ $p->sold_count }}</span>@endif
        @if ($p->brand)<a class="pbrand" href="{{ route('brand.show', $p->brand) }}">{{ $p->brand->name }}</a>@endif
    </div>

    @php $points = $p->shortFeatures(); @endphp
    @if ($points)
        <ul class="bfeat">@foreach ($points as $pt)<li>{{ $pt }}</li>@endforeach</ul>
    @elseif ($p->summary)
        <p class="bsum">{{ \Illuminate\Support\Str::limit($p->summary, 220) }}</p>
    @endif

    @foreach ($options as $slug => $opt)
        <div class="vopt" data-vopt="{{ $slug }}">
            <div class="vlabel">{{ $opt['attribute']->name }}: <b data-vchosen>Choose an option</b></div>
            <div @class(['vlist', 'colors' => $opt['attribute']->type === 'color'])>
                @foreach ($opt['values'] as $v)
                    <button type="button" class="vbtn2 {{ $opt['attribute']->type === 'color' ? 'is-color' : '' }}" data-vval="{{ $v->id }}" aria-pressed="false" @if ($v->color) style="--c: {{ $v->color }}" @endif>
                        {{ $v->value }}
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach
    @if ($options)
        <button class="clearv" type="button" data-vclear hidden><x-icon name="close" size="12" /> Clear</button>
        {{-- Price of the chosen option (Hara shows it again above "In stock"). Filled by safebuy.js. --}}
        <div class="bprice chosen" data-b-chosen hidden>
            <span class="now" data-b-price></span>
            <span class="was" data-b-old></span>
        </div>
    @endif

    <p @class(['bstock', 'out' => ! $p->inStock(), 'wait' => $p->isVariable()]) data-b-stock>
        @if ($p->isVariable())
            Choose an option to see stock
        @elseif ($stock === null || $stock > 0)
            <x-icon name="check" size="15" stroke="2.4" /> In stock{{ $stock !== null ? ' ('.$stock.')' : '' }}
        @else
            Out of stock
        @endif
    </p>

    <div class="bactions">
        <div class="qty big" data-bqty>
            <button type="button" aria-label="Decrease quantity" data-bq="-1"><x-icon name="minus" size="16" /></button>
            <input type="number" min="1" value="1" aria-label="Quantity" data-bq-input>
            <button type="button" aria-label="Increase quantity" data-bq="1"><x-icon name="plus" size="16" /></button>
        </div>
        <button class="btn orange badd" type="button" data-buy-add @disabled(! $p->inStock())><x-icon name="bag" size="17" /> Add to cart</button>
        <button class="btn blue" type="button" data-buy-now @disabled(! $p->inStock())>Buy now</button>
    </div>

    <div class="bsec">
        <button class="tlink" type="button" data-wish="{{ $p->id }}" data-wish-text><x-icon name="heart" size="16" /> <span>Add to wishlist</span></button>
        <button class="tlink" type="button" data-compare-add="{{ $p->id }}"><x-icon name="compare" size="16" /> <span>Add to compare</span></button>
    </div>

    <dl class="bmeta2">
        <div><dt>SKU:</dt><dd data-b-sku>{{ $p->isVariable() ? '—' : ($p->sku ?: '—') }}</dd></div>
        <div><dt>Category:</dt><dd><a href="{{ route('category.show', $p->category) }}">{{ $p->category->name }}</a></dd></div>
        @if ($p->tags->isNotEmpty())
            <div><dt>Tags:</dt><dd>@foreach ($p->tags as $t)<a href="{{ route('shop', ['tag' => [$t->slug]]) }}">{{ $t->name }}</a>@if (! $loop->last), @endif @endforeach</dd></div>
        @endif
        @if ($p->brand)
            <div><dt>Brand:</dt><dd><a href="{{ route('brand.show', $p->brand) }}">{{ $p->brand->name }}</a></dd></div>
        @endif
    </dl>

    @unless ($quick ?? false)
        <div class="share">
            <span>Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('product.show', $p)) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">f</a>
            <a href="https://wa.me/?text={{ urlencode($p->name.' '.route('product.show', $p)) }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp">wa</a>
            <button type="button" data-copy="{{ route('product.show', $p) }}" aria-label="Copy link"><x-icon name="arrows-lr" size="14" /></button>
        </div>
    @endunless

    @php
        $pBadges = isset($trustBadges) && $trustBadges->isNotEmpty()
            ? $trustBadges->map(fn ($b) => [$b->icon, $b->title])->all()
            : [
                ['truck', 'Delivery all over Bangladesh'],
                ['shield', '100% authentic, barcode verified'],
                ['card', 'Cash on delivery & bKash'],
            ];
    @endphp
    <ul class="trust">
        @foreach ($pBadges as [$bIcon, $bTitle])
            <li><x-icon :name="$bIcon" size="18" /> {{ $bTitle }}</li>
        @endforeach
    </ul>
</div>
