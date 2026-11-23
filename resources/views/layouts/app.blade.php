<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('safebuy.shop_name', 'SafeBuy').' - Beauty & Gadget In BD')</title>
    <meta name="description" content="@yield('description', 'Your trusted shop for 100% original Korean beauty and smart gadgets in Bangladesh.')">
    <link rel="icon" href="{{ asset(config('safebuy.favicon', 'images/brand/safebuy-logo.webp')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Dancing+Script:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/safebuy.css') }}?v={{ filemtime(public_path('css/safebuy.css')) }}">
    @php
        $sbColors = \App\Services\Settings::colors();
    @endphp
    @if ($sbColors !== \App\Services\Settings::DEFAULT_COLORS)
        <style>:root{--blue:{{ $sbColors['blue'] }};--blue-d:{{ $sbColors['blue_dark'] }};--blue-l:{{ $sbColors['blue_light'] }};--or:{{ $sbColors['orange'] }};--or-d:{{ $sbColors['orange_dark'] }};--or-l:{{ $sbColors['orange_light'] }};}</style>
    @endif
    {{-- Hide reveal sections only when JS runs; safebuy.js shows them as they scroll into view. --}}
    <script>if ('IntersectionObserver' in window) document.documentElement.classList.add('ready');</script>
</head>
<body class="sb">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-nav')

    {{-- One popup for Quick View and Compare; content is fetched into it. --}}
    <div class="modal" data-modal hidden>
        <button class="mscrim" type="button" aria-label="Close" data-modal-close tabindex="-1"></button>
        <div class="mbox" role="dialog" aria-modal="true" aria-label="Product details" tabindex="-1">
            <button class="mclose" type="button" aria-label="Close" data-modal-close><x-icon name="close" size="20" /></button>
            <div class="mbody" data-modal-body></div>
        </div>
    </div>

    <div class="lightbox" data-lightbox hidden>
        <button class="mclose" type="button" aria-label="Close image" data-lightbox-close><x-icon name="close" size="22" /></button>
        <img src="" alt="" data-lightbox-img>
    </div>

    @guest
        {{-- Sign-in popup from the header account icon (Hara style). --}}
        <div class="modal" data-login-modal hidden>
            <button class="mscrim" type="button" aria-label="Close" data-login-close tabindex="-1"></button>
            <div class="mbox login-box" role="dialog" aria-modal="true" aria-labelledby="t-signin" tabindex="-1">
                <button class="mclose" type="button" aria-label="Close" data-login-close><x-icon name="close" size="20" /></button>
                <h2 class="mtitle" id="t-signin">Sign in</h2>
                @include('auth._login-form', ['idp' => 'm', 'redirect' => url()->full()])
                <a class="btn ghost wide" href="{{ route('register', ['redirect' => url()->full()]) }}#register">Create an account</a>
            </div>
        </div>
    @endguest

    <div class="toast" role="status" aria-live="polite" data-toast></div>

    <button class="totop" type="button" aria-label="Back to top" data-totop>
        <x-icon name="arrow-up" size="20" />
    </button>

    {{-- Cart state for the header mini-cart (kept in the session on the server). --}}
    @php
        $sbData = [
            'cart' => app(\App\Services\Cart::class)->json(),
            'routes' => [
                'add' => route('cart.add'), 'update' => route('cart.update'), 'remove' => route('cart.remove'),
                'coupon' => route('cart.coupon'), 'shipping' => route('cart.shipping'), 'cart' => route('cart'),
                'checkout' => route('checkout'), 'compare' => route('compare'), 'suggest' => route('search.suggest'),
                'wishlist' => route('wishlist'), 'wishToggle' => route('wishlist.toggle'), 'wishSync' => route('wishlist.sync'),
            ],
            // Logged in: wishlist comes from the account; guests keep it in the browser.
            'user' => auth()->check(),
            'wish' => auth()->check() ? auth()->user()->wishlist()->pluck('product_id')->map(fn ($i) => (string) $i)->all() : null,
        ];
    @endphp
    <script>window.SB = @json($sbData);</script>
    <script src="{{ asset('js/safebuy.js') }}?v={{ filemtime(public_path('js/safebuy.js')) }}" defer></script>
</body>
</html>
