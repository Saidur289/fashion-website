@extends('layouts.app')

@section('title', 'Page Not Found — '.config('safebuy.shop_name', 'SafeBuy'))

@section('content')
@php
    $popular = \App\Models\Product::active()
        ->with(['brand', 'category', 'variants.values.attribute'])
        ->orderByDesc('sold_count')
        ->take(4)
        ->get();
@endphp
<div class="wrap cp-wrap">
    <div class="cp-404">
        <span class="cp-404-num">404</span>
        <h1>Oops — we couldn’t find that page</h1>
        <p class="cp-muted">The page may have moved or the link might be outdated. Try searching our catalog below.</p>
        <form class="cp-verify-box" action="{{ route('search') }}" method="get" style="max-width:520px;margin:20px auto">
            <input type="search" name="q" placeholder="Search authentic skincare &amp; gadgets…">
            <button class="btn orange" type="submit">Search</button>
        </form>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:16px">
            <a class="btn" href="{{ route('home') }}">Back to Home</a>
            <a class="btn ghost" href="{{ route('shop') }}">Browse Shop</a>
        </div>
    </div>

    @if ($popular->isNotEmpty())
        <section class="cp-sec">
            <div class="phead">
                <h2 class="stitle sm">Popular on SafeBuy</h2>
                <a class="vall" href="{{ route('shop') }}">View all <x-icon name="arrow-right" size="14" /></a>
            </div>
            <div class="panel">
                <ul class="track">
                    @foreach ($popular as $prod)
                        <x-product-card :product="$prod" :index="$loop->index" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</div>
@endsection
