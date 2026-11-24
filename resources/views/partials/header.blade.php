@php
    $nav = isset($headerMenu) && $headerMenu->isNotEmpty()
        ? $headerMenu->map(fn ($m) => [$m->label, str_starts_with($m->url, 'http') ? $m->url : url($m->url), false, $m->open_in_new_tab])->all()
        : [
            ['Home', route('home'), false, false],
            ['Shop', route('shop'), false, false],
            ['K-Beauty', route('shop', ['group' => 'beauty']), false, false],
            ['Smart Gadgets', route('shop', ['group' => 'gadget']), false, false],
            ['Concierge', route('concierge'), false, false],
            ['Blog', route('blog.index'), false, false],
            ['About', route('about'), false, false],
            ['FAQ', route('faq'), false, false],
            ['Contact', route('contact'), false, false],
        ];

    // Hara-style dropdowns: menu links that point at the shop get a list of matching categories.
    $dropFor = function (string $href) use ($menuCategories) {
        $path = parse_url($href, PHP_URL_PATH) ?? '';
        if (! str_ends_with(rtrim($path, '/'), '/shop')) {
            return collect();
        }
        parse_str((string) parse_url($href, PHP_URL_QUERY), $q);

        return isset($q['group']) ? $menuCategories->where('group', $q['group']) : $menuCategories;
    };

    $social = array_filter((array) config('safebuy.social', []));
    $phone = config('safebuy.phone');
    $socialIcons = ['facebook' => 'F', 'instagram' => 'IG', 'youtube' => 'YT', 'whatsapp' => 'WA'];
@endphp

{{-- Row 1: Auto-sliding announcement bar (email & address remain in footer) --}}
@php
    $announcement = trim((string) config('safebuy.topbar_text', 'Welcome to SafeBuy — 100% authentic K-Beauty & smart gadgets, delivered to your door'));
@endphp
@if ($announcement !== '')
<div class="htop" role="region" aria-label="Announcement">
    <div class="htop-marquee">
        <div class="htop-track">
            @for ($i = 0; $i < 6; $i++)
                <span class="htop-item" @if ($i > 0) aria-hidden="true" @endif>{{ $announcement }}<em>◆</em></span>
            @endfor
        </div>
    </div>
</div>
@endif

<header class="hdr" data-header>
    {{-- Row 2: logo, category + search, call us, icons --}}
    <div class="wrap hrow">
        <button class="ibtn menuBtn" type="button" aria-label="Open menu" aria-expanded="false" data-menu-open>
            <x-icon name="menu" size="22" />
        </button>
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset(config('safebuy.logo', 'images/brand/safebuy-logo.webp')) }}" alt="{{ config('safebuy.shop_name', 'SafeBuy') }}" width="150" height="48">
        </a>
        <form class="search" role="search" action="{{ route('search') }}" method="get" data-live-search>
            <label class="vh" for="qc1">Category</label>
            <span class="catsel">
                <select id="qc1" name="cat">
                    <option value="">All Categories</option>
                    @foreach ($menuCategories as $c)
                        <option value="{{ $c->slug }}">{{ $c->name }} ({{ $c->products_count }})</option>
                    @endforeach
                </select>
                <x-icon name="chevron-down" size="14" />
            </span>
            <label class="vh" for="q1">Search products</label>
            <input id="q1" name="q" type="search" placeholder="I'm looking for… ({{ $menuCategories->sum('products_count') }}+ products)">
            <button type="submit" aria-label="Search"><x-icon name="search" size="18" /></button>
            <div class="suggest" data-suggest hidden></div>
        </form>
        @if ($phone)
            <a class="hcall" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">
                <span class="hcall-i"><x-icon name="headset" size="20" /></span>
                <span><small>Call us free</small><b>{{ $phone }}</b></span>
            </a>
        @endif
        <div class="icons">
            @auth
                <a class="ibtn hide-m" href="{{ route('account') }}" aria-label="My account ({{ auth()->user()->name }})"><x-icon name="user" size="21" stroke="1.7" /><span class="dot-on" aria-hidden="true"></span></a>
            @else
                <a class="ibtn hide-m" href="{{ route('login') }}" aria-label="Log in" data-login-open><x-icon name="user" size="21" stroke="1.7" /></a>
            @endauth
            <a class="ibtn hide-m" href="{{ route('compare') }}" aria-label="Compare" data-compare-link><x-icon name="compare" size="21" stroke="1.7" /><span class="badge" data-compare-count>0</span></a>
            <a class="ibtn hide-m" href="{{ route('wishlist') }}" aria-label="Wishlist" data-wish-link><x-icon name="heart" size="21" stroke="1.7" /><span class="badge" data-wish-count>0</span></a>
            <div class="pop-wrap" data-pop>
                <button class="ibtn" type="button" aria-label="Cart" aria-expanded="false" data-pop-btn><x-icon name="bag" size="21" stroke="1.7" /><span class="badge" data-cart-count>0</span></button>
                <div class="pop minicart" data-pop-panel data-minicart hidden>
                    <div class="pop-h">Your cart</div>
                    <ul class="mini-list" data-mc-items></ul>
                    <div class="pop-empty" data-mc-empty>
                        <p>Your cart is empty.</p>
                        <a class="tlink" href="{{ route('shop') }}">Continue shopping <x-icon name="arrow-right" size="14" /></a>
                    </div>
                    <div class="mc-foot" data-mc-foot>
                        <div class="mc-sub"><span>Subtotal</span><b data-mc-sub>Tk 0</b></div>
                        <div class="ship">
                            <div class="ship-bar"><i data-mc-bar><em data-mc-pct></em></i></div>
                            <p data-mc-ship></p>
                        </div>
                        <div class="mc-btns">
                            <a class="btn blue" href="{{ route('cart') }}">View cart</a>
                            <a class="btn orange" href="{{ route('checkout') }}">Checkout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: All Departments, main menu with dropdowns, Recently Viewed --}}
    <div class="hnav">
        <div class="wrap hnav-in">
            <div class="dept pop-wrap" data-pop>
                <button class="dept-btn" type="button" aria-expanded="false" data-pop-btn>
                    <x-icon name="grid" size="18" /> All Departments <x-icon name="chevron-down" size="14" />
                </button>
                <div class="pop dept-pop" data-pop-panel hidden>
                    <ul>
                        @foreach ($menuCategories as $c)
                            <li><a href="{{ route('category.show', $c) }}"><img src="{{ asset($c->image) }}" alt="" width="34" height="34" loading="lazy"> <span>{{ $c->name }}</span> <em>{{ $c->products_count }}</em></a></li>
                        @endforeach
                        <li class="dept-all"><a href="{{ route('shop') }}">View all products <x-icon name="arrow-right" size="14" /></a></li>
                    </ul>
                </div>
            </div>
            <nav class="nav" aria-label="Main">
                <ul>
                    @foreach ($nav as [$label, $href, $soon, $newTab])
                        @php $drop = $dropFor($href); @endphp
                        <li @class(['has-drop' => $drop->isNotEmpty()])>
                            <a href="{{ $href }}" @class(['cur' => $href === url()->current() || ($loop->first && request()->routeIs('home'))]) @if ($soon) data-soon @endif @if ($newTab) target="_blank" rel="noopener" @endif>{{ $label }}@if ($drop->isNotEmpty()) <x-icon name="chevron-down" size="13" />@endif</a>
                            @if ($drop->isNotEmpty())
                                <ul class="subnav">
                                    @foreach ($drop as $c)<li><a href="{{ route('category.show', $c) }}">{{ $c->name }}</a></li>@endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
            <div class="pop-wrap recent-wrap" data-pop>
                <button class="recent-btn" type="button" aria-expanded="false" data-pop-btn>Recently Viewed <x-icon name="chevron-down" size="14" /></button>
                <div class="pop recent" data-pop-panel hidden>
                    <div class="pop-h">Recently viewed</div>
                    <ul class="mini-list" data-recent-list></ul>
                    <p class="pop-empty" data-recent-empty>Products you open will show up here.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="msearch">
        <form class="search" role="search" action="{{ route('search') }}" method="get" data-live-search>
            <label class="vh" for="q2">Search products</label>
            <input id="q2" name="q" type="search" placeholder="Search in {{ $menuCategories->sum('products_count') }}+ products…">
            <button type="submit" aria-label="Search"><x-icon name="search" size="18" /></button>
            <div class="suggest" data-suggest hidden></div>
        </form>
    </div>
</header>

<div class="menu-wrap" data-menu>
    <button class="scrim" type="button" aria-label="Close menu" data-menu-close tabindex="-1"></button>
    <aside class="drawer" aria-label="Menu">
        <div class="drawer-top">
            <img src="{{ asset(config('safebuy.logo', 'images/brand/safebuy-logo.webp')) }}" alt="{{ config('safebuy.shop_name', 'SafeBuy') }}" height="34" width="106">
            <button class="ibtn" type="button" aria-label="Close menu" data-menu-close><x-icon name="close" size="20" /></button>
        </div>
        <div class="dtabs" role="tablist" aria-label="Menu sections">
            <button class="dtab on" type="button" role="tab" aria-selected="true" data-dtab="main">Main Menu</button>
            <button class="dtab" type="button" role="tab" aria-selected="false" data-dtab="dept">All Departments</button>
        </div>
        <ul data-dpanel="main">
            @foreach ($nav as [$label, $href, $soon, $newTab])
                <li style="--sd: {{ 0.1 + $loop->index * 0.06 }}s"><a href="{{ $href }}" @if ($soon) data-soon @endif @if ($newTab) target="_blank" rel="noopener" @endif>{{ $label }}</a></li>
            @endforeach
        </ul>
        <ul data-dpanel="dept" hidden>
            @foreach ($menuCategories as $c)
                <li style="--sd: {{ 0.05 + $loop->index * 0.05 }}s"><a href="{{ route('category.show', $c) }}">{{ $c->name }} <small>({{ $c->products_count }})</small></a></li>
            @endforeach
        </ul>
        @if ($phone)
            <a class="hcall drawer-call" href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}"><span class="hcall-i"><x-icon name="headset" size="18" /></span><span><small>Call us free</small><b>{{ $phone }}</b></span></a>
        @endif
    </aside>
</div>
