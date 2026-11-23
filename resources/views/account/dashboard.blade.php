@extends('layouts.app')

@section('title', 'My account - SafeBuy')

@section('content')
@include('partials.page-head', ['title' => 'My Account', 'crumbs' => [['My account', null]]])
<div class="wrap sec-in acc">
    @include('account._shell', ['active' => 'dashboard'])
    <div class="acc-main">
        @if (session('status'))<p class="alert ok">{{ session('status') }}</p>@endif
        <p class="lead2">From your account you can check your orders, manage delivery addresses and change your password.</p>
        <ul class="acc-stats">
            <li><a href="{{ route('account.orders') }}"><x-icon name="bag" size="24" /><b>{{ $counts['orders'] }}</b><span>{{ \Illuminate\Support\Str::plural('Order', $counts['orders']) }}</span></a></li>
            <li><a href="{{ route('wishlist') }}"><x-icon name="heart" size="24" /><b>{{ $counts['wishlist'] }}</b><span>In wishlist</span></a></li>
            <li><a href="{{ route('account.addresses') }}"><x-icon name="pin" size="24" /><b>{{ $counts['addresses'] }}</b><span>Saved {{ \Illuminate\Support\Str::plural('address', $counts['addresses']) }}</span></a></li>
        </ul>

        <h2 class="acc-h">Recent orders</h2>
        @include('account._orders-table', ['orders' => $recent])
        @if ($recent->isNotEmpty())<a class="tlink" href="{{ route('account.orders') }}">See all orders <x-icon name="arrow-right" size="14" /></a>@endif
    </div>
</div>
@endsection
