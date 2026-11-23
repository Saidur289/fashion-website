@extends('layouts.app')

@section('title', 'Cart - SafeBuy')

@section('content')
    @include('partials.page-head', ['title' => 'Shopping Cart', 'crumbs' => [['Cart', null]]])

    <div class="wrap sec-in" data-cart-page>
        @include('cart._body')
    </div>

    @if ($suggested->isNotEmpty())
        <section class="wrap rv" data-reveal aria-labelledby="t-may" style="padding-bottom: 80px">
            <div class="phead"><h2 class="stitle sm" id="t-may">You may also like</h2></div>
            <div class="panel">
                <ul class="track">
                    @foreach ($suggested as $p)
                        <x-product-card :product="$p" :index="$loop->index" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
