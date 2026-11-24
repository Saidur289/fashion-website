@extends('layouts.app')

@section('title', 'Wishlist - SafeBuy')

@section('content')
@php use App\Models\Product; @endphp
@include('partials.page-head', ['title' => 'Wishlist', 'crumbs' => [['Wishlist', null]]])

<div class="wrap sec-in" data-wishlist-page @if ($guest) data-guest @endif>
    @if ($guest)
        <p class="alert">Your wishlist is saved in this browser. <a class="tlink" href="{{ route('login', ['redirect' => route('wishlist')]) }}">Log in</a> to keep it on your account.</p>
    @endif

    <div class="empty" data-wish-empty @if ($items->isNotEmpty()) hidden @endif>
        <x-icon name="heart" size="40" />
        <h2>No products added to the wishlist</h2>
        <p>Tap the heart on any product to save it here.</p>
        <a class="btn orange" href="{{ route('shop') }}">Browse the shop <x-icon name="arrow-right" size="16" /></a>
    </div>

    @if ($items->isNotEmpty())
        <ul class="wgrid" data-stagger="0.06">
            @foreach ($items as ['product' => $p, 'variant' => $v])
                @php
                    $price = $v ? $v->price : $p->minPrice();
                    $inStock = $v ? $v->inStock() : $p->inStock();
                    $data = $p->cardData();
                @endphp
                <li class="wcard" data-product='@json($data)'>
                    <button class="cmp-x" type="button" data-wish="{{ $p->id }}" data-wish-remove aria-label="Remove {{ $p->name }} from wishlist"><x-icon name="trash" size="15" /></button>
                    <a href="{{ route('product.show', $p) }}"><img src="{{ asset($v?->image ?: $p->image) }}" alt="" width="300" height="300" loading="lazy" decoding="async"></a>
                    <span @class(['avail', 'out' => ! $inStock])>{{ $inStock ? 'In stock' : 'Out of stock' }}</span>
                    <a class="wname" href="{{ route('product.show', $p) }}">{{ $p->name }}@if ($v) <small>– {{ $v->label() }}</small>@endif</a>
                    <b class="wprice">{{ $v ? Product::taka($v->price) : $p->priceLabel() }}</b>
                    @if (! $inStock)
                        <a class="btn ghost wide sm" href="{{ route('product.show', $p) }}">View product</a>
                    @elseif ($p->isVariable() && ! $v)
                        <a class="btn blue wide sm" href="{{ route('product.show', $p) }}">Select options</a>
                    @else
                        <button class="btn orange wide sm" type="button" data-add="{{ $p->id }}" @if ($v) data-variant="{{ $v->id }}" @endif><x-icon name="bag" size="15" /><span>Add to cart</span></button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
