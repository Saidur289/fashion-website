@extends('layouts.app')

@section('title', ($product->meta_title ?: $product->name).' - SafeBuy')
@section('description', $product->meta_description ?: \Illuminate\Support\Str::limit($product->summary, 155))

@section('content')
@php
    use App\Models\Product;
    $p = $product;
    $bundle = collect([$p])->merge($p->boughtTogether)->take(3);
    $specs = collect($p->specs ?? []);
    foreach ($p->attributeValues->groupBy(fn ($v) => $v->attribute->name) as $name => $values) {
        $specs->put($name, $values->pluck('value')->implode(', '));
    }
    if ($p->origin) { $specs->put('Origin', $p->origin); }
    if ($p->skin_type) { $specs->put('Skin type', $p->skin_type); }
    if ($p->volume) { $specs->put('Volume', $p->volume); }
@endphp

{{-- sticky bar that appears once the buy box scrolls away --}}
<div class="pbar" data-pbar aria-hidden="true">
    <div class="wrap pbar-in">
        <img src="{{ asset($p->image) }}" alt="" width="52" height="52">
        <div class="pbar-t">
            <b>{{ $p->name }}</b>
            <span><span class="srow" style="--r: {{ ($p->rating ?? 0) * 20 }}%">★★★★★</span> <em>{{ $p->priceLabel() }}</em></span>
        </div>
        <button class="btn orange" type="button" data-pbar-add tabindex="-1">{{ $p->isVariable() ? 'Select options' : 'Add to cart' }}</button>
    </div>
</div>

<div class="wrap pdp" data-pdp data-recent='@json($p->cardData())'>
    <div class="pdp-top">
        <nav class="crumbs dark" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span>
            <a href="{{ route('category.show', $p->category) }}">{{ $p->category->name }}</a><span aria-hidden="true">/</span>
            <span aria-current="page">{{ \Illuminate\Support\Str::limit($p->name, 40) }}</span>
        </nav>
        <div class="pnav">
            <a href="{{ route('product.show', $prev) }}" aria-label="Previous product: {{ $prev->name }}"><x-icon name="arrow-left" size="16" /> Prev</a>
            <a href="{{ route('product.show', $next) }}" aria-label="Next product: {{ $next->name }}">Next <x-icon name="arrow-right" size="16" /></a>
        </div>
    </div>

    <div class="pdp-main" data-motion="fade-up">
        @include('product._gallery', ['product' => $p])
        @include('product._buybox', ['product' => $p])
    </div>

    {{-- Frequently bought together --}}
    @if ($bundle->count() > 1)
        <section class="fbt rv" data-reveal aria-labelledby="t-fbt" data-fbt>
            <h2 id="t-fbt" class="stitle sm">Frequently Bought Together</h2>
            <div class="fbt-in">
                <ul class="fbt-items">
                    @foreach ($bundle as $b)
                        <li class="st" style="--sd: {{ $loop->index * 0.12 }}s">
                            @unless ($loop->first)<span class="fplus" aria-hidden="true">+</span>@endunless
                            <label class="fitem" data-product='@json($b->cardData())'>
                                <input type="checkbox" checked data-fbt-item data-price="{{ $b->minPrice() }}" @disabled(! $b->inStock())>
                                <img src="{{ asset($b->image) }}" alt="" width="140" height="140" loading="lazy">
                                <span class="fname">{{ $loop->first ? 'This item: ' : '' }}{{ $b->name }}</span>
                                <b>{{ $b->isVariable() ? 'From '.Product::taka($b->minPrice()) : Product::taka($b->price) }}</b>
                            </label>
                        </li>
                    @endforeach
                </ul>
                <div class="fbt-total">
                    <span>Total price</span>
                    <b data-fbt-total>{{ Product::taka($bundle->sum(fn ($b) => $b->minPrice())) }}</b>
                    <button class="btn orange" type="button" data-fbt-add>Add all to cart</button>
                    <small>Items with sizes are added in their smallest size. You can change it in the cart.</small>
                </div>
            </div>
        </section>
    @endif

    {{-- Tabs --}}
    <section class="ptabs" data-motion="fade-up" aria-label="Product information" id="reviews">
        <div class="ptab-list" role="tablist">
            <button class="ptab on" role="tab" aria-selected="true" data-ptab="desc">Description</button>
            @if ($p->how_to_use)<button class="ptab" role="tab" aria-selected="false" data-ptab="use">How to use</button>@endif
            @if ($specs->isNotEmpty())<button class="ptab" role="tab" aria-selected="false" data-ptab="info">Additional information</button>@endif
            <button class="ptab" role="tab" aria-selected="false" data-ptab="reviews">Reviews ({{ $p->reviews_count }})</button>
        </div>
        <div class="ptab-panel prose" role="tabpanel" data-ppanel="desc">
            @if ($p->summary)<p class="lead">{{ $p->summary }}</p>@endif
            @include('product._blocks', ['blocks' => $p->description])
            @if ($p->ingredients)
                <h3>Ingredients</h3>
                <p>{{ $p->ingredients }}</p>
            @endif
        </div>
        @if ($p->how_to_use)
            <div class="ptab-panel prose" role="tabpanel" data-ppanel="use" hidden>
                @include('product._blocks', ['blocks' => $p->how_to_use])
            </div>
        @endif
        @if ($specs->isNotEmpty())
            <div class="ptab-panel" role="tabpanel" data-ppanel="info" hidden>
                <table class="specs">
                    @foreach ($specs as $k => $v)
                        <tr><th scope="row">{{ $k }}</th><td>{{ $v }}</td></tr>
                    @endforeach
                </table>
            </div>
        @endif
        <div class="ptab-panel" role="tabpanel" data-ppanel="reviews" @unless (session('review_status') || session('review_error') || $errors->hasAny(['rating', 'body', 'photos', 'photos.*'])) hidden @endunless>
            @include('product._reviews')
        </div>
    </section>

    {{-- Related --}}
    @if ($related->isNotEmpty())
        <section class="rv related" data-reveal aria-labelledby="t-rel">
            <div class="phead">
                <h2 class="stitle sm" id="t-rel">Related products</h2>
                <a class="vall" href="{{ route('category.show', $p->category) }}">More {{ $p->category->name }} <x-icon name="arrow-right" size="14" /></a>
            </div>
            <div class="panel">
                <ul class="track">
                    @foreach ($related as $r)
                        <x-product-card :product="$r" :index="$loop->index" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</div>
@endsection
