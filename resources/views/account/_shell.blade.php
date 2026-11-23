{{-- Account sidebar + content. Usage: @include('account._shell', ['active' => 'orders']) then the page content. --}}
@php
    $links = [
        'dashboard' => ['Dashboard', route('account'), 'home'],
        'orders' => ['Orders', route('account.orders'), 'bag'],
        'addresses' => ['Addresses', route('account.addresses'), 'pin'],
        'details' => ['Account details', route('account.details'), 'user'],
        'wishlist' => ['Wishlist', route('wishlist'), 'heart'],
    ];
@endphp
<nav class="acc-nav" aria-label="My account">
    <div class="acc-hi">
        <span class="acc-av" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <div><small>Hello,</small><b>{{ auth()->user()->name }}</b></div>
    </div>
    <ul>
        @foreach ($links as $key => [$label, $href, $icon])
            <li><a href="{{ $href }}" @class(['on' => $active === $key]) @if ($active === $key) aria-current="page" @endif><x-icon :name="$icon" size="18" /> {{ $label }}</a></li>
        @endforeach
        <li>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="arrow-left" size="18" /> Log out</button></form>
        </li>
    </ul>
</nav>
