@extends('layouts.app')

@section('content')

@foreach ($sections as $section)
    @switch($section->key)

        @case('hero')
            {{-- ================= HERO SLIDER (HARA STYLE) ================= --}}
            @php
                $slidesList = $heroSlides->isNotEmpty() ? $heroSlides : collect([
                    (object) [
                        'eyebrow' => 'Original Korean Care',
                        'title' => "Get The Perfectly\nHydrated Skin",
                        'description' => '100% authentic Korean & Japanese skincare, delivered across Bangladesh.',
                        'cta_label' => 'Shop now',
                        'cta_url' => route('shop'),
                        'bg_image' => null,
                        'image_primary' => 'images/hero/hara/s1-model.webp',
                        'image_secondary' => 'images/hero/hara/s1-products.webp',
                        'layout_style' => 'split',
                        'accent_color' => '#F5E9D9',
                    ],
                    (object) [
                        'eyebrow' => 'Body & Hair',
                        'title' => "Clean Matte Liquid\nFoundation",
                        'description' => 'Lightweight, breathable coverage that lasts all day — verified original.',
                        'cta_label' => 'Shop now',
                        'cta_url' => route('shop', ['group' => 'beauty']),
                        'bg_image' => null,
                        'image_primary' => 'images/hero/hara/s2-arch-product.webp',
                        'image_secondary' => 'images/hero/hara/s2-leaves.webp',
                        'layout_style' => 'arch',
                        'accent_color' => '#ECECEC',
                    ],
                    (object) [
                        'eyebrow' => 'Spring Day',
                        'title' => "Changing The Ideal of\nBeauty",
                        'description' => 'Serums, day creams and night creams that protect, moisturise and nourish your skin — sourced straight from trusted brands.',
                        'cta_label' => 'Shop now',
                        'cta_url' => route('shop', ['group' => 'beauty']),
                        'bg_image' => null,
                        'image_primary' => 'images/hero/hara/s3-product.webp',
                        'image_secondary' => null,
                        'layout_style' => 'rings',
                        'accent_color' => '#ECECEC',
                    ],
                ]);
            @endphp
            <section class="hero" aria-label="Featured" data-hero data-interval="9">
                @foreach ($slidesList as $slide)
                    @php
                        $styleKey = match ($slide->layout_style) {
                            'split' => 's1',
                            'rings' => 's3',
                            default => 's2',
                        };
                        $defaultBg = $styleKey === 's1' ? '#F5E9D9' : '#ECECEC';
                        $bgColor = $slide->accent_color ?: $defaultBg;
                        $slideStyle = 'background-color: ' . $bgColor . ';';
                        if (! empty($slide->bg_image)) {
                            $slideStyle .= " background-image: url('" . asset($slide->bg_image) . "');";
                        }
                        $ctaHref = str_starts_with((string) $slide->cta_url, 'http') ? $slide->cta_url : url($slide->cta_url ?: '/shop');
                    @endphp
                    <div class="slide slide--{{ $styleKey }} @if ($loop->first) on @endif" style="{{ $slideStyle }}">
                        <div class="slide-inner">
                            @if ($styleKey === 's1')
                                @if ($slide->image_primary)
                                    <div class="hero-layer s1-model-wrap">
                                        <div class="anim-float-x">
                                            <img src="{{ asset($slide->image_primary) }}?v={{ @filemtime(public_path($slide->image_primary)) ?: 1 }}" alt="{{ $slide->title }}" decoding="async" @if ($loop->first) fetchpriority="high" @endif>
                                        </div>
                                    </div>
                                @endif
                                @if ($slide->image_secondary)
                                    <div class="hero-layer s1-products-wrap">
                                        <div class="anim-float-x-slow">
                                            <img src="{{ asset($slide->image_secondary) }}?v={{ @filemtime(public_path($slide->image_secondary)) ?: 1 }}" alt="{{ $slide->title }}" decoding="async" @if ($loop->first) fetchpriority="high" @endif>
                                        </div>
                                    </div>
                                @endif
                            @elseif ($styleKey === 's3')
                                @if ($slide->image_primary)
                                    <div class="hero-layer s3-product-wrap">
                                        <div class="anim-float-x" style="height: 100%">
                                            <img src="{{ asset($slide->image_primary) }}" alt="{{ $slide->title }}" decoding="async" @if ($loop->first) fetchpriority="high" @endif>
                                        </div>
                                    </div>
                                @endif
                                <div class="hero-layer s3-leaf s3-leaf-1" aria-hidden="true">
                                    <div class="anim-leaf-rock">
                                        <img src="{{ asset('images/hero/hara/s3-leaf-a.webp') }}" alt="" decoding="async">
                                    </div>
                                </div>
                                <div class="hero-layer s3-leaf s3-leaf-2" aria-hidden="true">
                                    <div class="anim-leaf-drift">
                                        <img src="{{ asset('images/hero/hara/s3-leaf-b.webp') }}" alt="" decoding="async">
                                    </div>
                                </div>
                                <div class="hero-layer s3-leaf s3-leaf-3" aria-hidden="true">
                                    <div class="anim-leaf-rock">
                                        <img src="{{ asset('images/hero/hara/s3-leaf-b.webp') }}" alt="" decoding="async">
                                    </div>
                                </div>
                            @else
                                @php
                                    $leavesImg = $slide->image_secondary ?: 'images/hero/hara/s2-leaves.webp';
                                @endphp
                                <div class="hero-layer s2-leaves-wrap" aria-hidden="true">
                                    <div class="anim-leaves-overlay" style="width: 100%; height: 100%">
                                        <img src="{{ asset($leavesImg) }}" alt="" decoding="async">
                                    </div>
                                </div>
                                @if ($slide->image_primary)
                                    <div class="hero-layer s2-arch-wrap">
                                        <div class="anim-float-x" style="height: 100%">
                                            <img src="{{ asset($slide->image_primary) }}" alt="{{ $slide->title }}" decoding="async" @if ($loop->first) fetchpriority="high" @endif>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <div class="hero-content">
                                <div class="hero-text-box">
                                    @if ($slide->eyebrow)<div class="hero-eyebrow a-eye">{{ $slide->eyebrow }}</div>@endif
                                    @if ($loop->first)
                                        <h1 class="hero-title a-tit">{!! nl2br(e($slide->title)) !!}</h1>
                                    @else
                                        <h2 class="hero-title a-tit">{!! nl2br(e($slide->title)) !!}</h2>
                                    @endif
                                    @if ($slide->description)<p class="hero-desc a-desc">{{ $slide->description }}</p>@endif
                                    <a class="hero-btn a-btn" href="{{ $ctaHref }}">
                                        <span>{{ $slide->cta_label ?: 'Shop now' }}</span>
                                        <x-icon name="arrow-right-circle" size="18" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($slidesList->count() > 1)
                    <div class="hero-bullets" role="tablist" aria-label="Choose a slide">
                        @foreach ($slidesList as $slide)
                            <button class="hero-bullet @if ($loop->first) on @endif" type="button" aria-label="Show slide: {{ $slide->eyebrow ?: $slide->title }}" data-hero-dot="{{ $loop->index }}"></button>
                        @endforeach
                    </div>
                @endif
            </section>
            @break

        @case('categories')
            {{-- ================= BEAUTY & LIFESTYLE ESSENTIALS (safebuystore.com match) ================= --}}
            <section class="sb-mid-sec sb-ess rv" data-reveal aria-labelledby="t-cats" id="beauty-essentials">
                <div class="sb-mid-wrap">
                    <div class="sb-ess-head">
                        <h2 class="sb-sec-title" id="t-cats">{{ $section->title ?: 'Beauty & Lifestyle Essentials' }}</h2>
                        <svg class="sb-wave" viewBox="0 0 64 12" fill="none" aria-hidden="true">
                            <path d="M2 7c6-6 12-6 18 0s12 6 18 0 12-6 18 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="sb-ess-grid">
                        <a class="sb-ess-card st" style="--sd: .06s" href="{{ route('shop', ['group' => 'beauty']) }}">
                            <div class="sb-ess-img">
                                <img src="{{ asset('images/banners/cat-kbeauty.webp') }}" alt="K-Beauty" loading="lazy" width="600" height="600">
                            </div>
                            <div class="sb-ess-body">
                                <h3>K-Beauty</h3>
                                <p>100% Authentic Care</p>
                                <span class="sb-ess-cta">Shop now</span>
                            </div>
                        </a>
                        <a class="sb-ess-card st" style="--sd: .14s" href="{{ route('shop', ['group' => 'gadget']) }}">
                            <div class="sb-ess-img">
                                <img src="{{ asset('images/banners/cat-lifestyle.webp') }}" alt="Smart Lifestyle" loading="lazy" width="600" height="600">
                            </div>
                            <div class="sb-ess-body">
                                <h3>Smart Lifestyle</h3>
                                <p>Upgrade Your Life</p>
                                <span class="sb-ess-cta">Shop now</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
            @break

        @case('promo')
            {{-- ================= PROMO: SMART CHOICE, SAFE SHOPPING (safebuystore.com match) ================= --}}
            <section class="sb-mid-sec sb-promo-sec rv" data-reveal aria-labelledby="t-promo">
                <div class="sb-mid-wrap">
                    <div class="sb-promo-card">
                        <div class="sb-promo-grid">
                            <div class="sb-promo-copy">
                                <h2 class="sb-promo-title st" id="t-promo" style="--sd: .1s">{{ $promoBanner?->eyebrow ?: 'Smart Choice, Safe Shopping' }}</h2>
                                <div class="sb-promo-sub st" style="--sd: .22s">
                                    <span class="sb-promo-star" aria-hidden="true">✦</span>
                                    <span class="sb-promo-line" aria-hidden="true"></span>
                                    <span>{{ $promoBanner?->title ?: 'Where Premium K-Beauty Meets Next-Gen Gadgets.' }}</span>
                                </div>
                                <p class="sb-promo-desc st" style="--sd: .34s">{{ $promoBanner?->subtitle ?: 'Get the best of both worlds. Grab 100% original Korean skincare and premium tech gadgets at unbeatable prices.' }}</p>
                                <a class="sb-promo-btn st" style="--sd: .46s" href="{{ $promoBanner?->cta_url ? url($promoBanner->cta_url) : route('shop', ['on_sale' => 1]) }}">
                                    <span>{{ $promoBanner?->cta_label ?: 'Shop now' }}</span>
                                    <x-icon name="arrow-right" size="16" />
                                </a>
                            </div>
                            <div class="sb-promo-art st" style="--sd: .25s">
                                <img class="sb-sway" src="{{ asset($promoBanner?->image ?: 'images/banners/offer-right.png') }}" alt="K-Beauty serum, cream, smart watch, earbuds and headphones" loading="lazy" width="577" height="433">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('products_tabs')
            {{-- ================= PRODUCT SHOWCASES (New Arrival, Trending K-Beauty, Trending Smart Gadgets — safebuystore.com match) ================= --}}
            @foreach ($tabs as $tab)
                @if ($tab['products']->isNotEmpty())
                <section class="sb-mid-sec sb-prod-sec @if ($loop->first) is-first @endif @if ($loop->last) is-last @endif rv" data-reveal aria-labelledby="t-prod-{{ $tab['key'] }}">
                    <div class="sb-mid-wrap">
                        <div class="sb-prod-head">
                            <h2 class="sb-sec-title" id="t-prod-{{ $tab['key'] }}">{{ $tab['label'] }}</h2>
                            <a class="sb-view-all" href="{{ $tab['url'] ?? route('shop') }}">View all <x-icon name="arrow-right" size="15" /></a>
                        </div>
                        <ul class="sb-grid4">
                            @foreach ($tab['products'] as $product)
                                <x-product-card :product="$product" :index="$loop->index" />
                            @endforeach
                        </ul>
                    </div>
                </section>
                @endif
            @endforeach
            @break

        @case('best_selling')
            {{-- ================= BEST SELLING ================= --}}
            @if ($bestSellers->isNotEmpty())
            <section class="sec best rv" data-reveal aria-labelledby="t-best">
                <div class="wrap">
                    <div class="phead">
                        <div>
                            <div class="eyebrow">{{ $section->eyebrow ?: 'Customer favourites' }}</div>
                            <h2 class="stitle" id="t-best" style="margin-top: 8px">{{ $section->title ?: 'Best Selling' }}</h2>
                        </div>
                        <a class="vall" href="{{ route('shop', ['sort' => 'popularity']) }}">View all products <x-icon name="arrow-right" size="14" /></a>
                    </div>
                    <div class="panel">
                        <ul class="track">
                            @foreach ($bestSellers as $product)
                                <x-product-card :product="$product" :index="$loop->index" />
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('why_shop')
            {{-- ================= WHY SHOP ================= --}}
            @php
                $whyList = $whyBadges->isNotEmpty()
                    ? $whyBadges->map(fn ($b) => [$b->icon, $b->title, $b->subtitle])->all()
                    : [
                        ['shield', '100% Authentic', 'Only genuine products.'],
                        ['globe', 'Global Sourcing', 'Direct from Seoul & Tokyo.'],
                        ['barcode', 'Safe & Secure', 'Barcode verified & trackable.'],
                        ['headset', 'Concierge Support', 'Request to doorstep.'],
                    ];
            @endphp
            <section class="sec why rv" data-reveal aria-labelledby="t-why">
                <div class="wrap">
                    <div class="shead">
                        <h2 class="stitle" id="t-why">{{ $section->title ?: 'Why Shop with SafeBuy?' }}</h2>
                        <x-squiggle />
                    </div>
                    <ul class="feats">
                        @foreach ($whyList as [$icon, $title, $text])
                            <li class="feat st" style="--sd: {{ 0.1 + $loop->index * 0.15 }}s">
                                <div class="ficon" style="--sd: {{ 0.2 + $loop->index * 0.15 }}s"><x-icon :name="$icon" size="34" stroke="1.5" /></div>
                                <h3>{{ $title }}</h3>
                                <p>{{ $text }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
            @break

        @case('twin_banners')
            {{-- ================= TWIN BANNERS ================= --}}
            <section class="rv" data-reveal aria-label="Shop by world">
                <div class="wrap bans">
                    <a class="ban {{ $twinLeftBanner?->theme ?: 'light' }} st" href="{{ $twinLeftBanner?->cta_url ? url($twinLeftBanner->cta_url) : route('shop', ['group' => 'beauty']) }}" style="--sd: .1s">
                        <img class="ban-bg" src="{{ asset($twinLeftBanner?->image ?: 'images/banners/twin-left.png') }}" alt="" loading="lazy">
                        <div class="bt">
                            <div class="eyebrow">{{ $twinLeftBanner?->eyebrow ?: 'Korean Beauty' }}</div>
                            <h3>{{ $twinLeftBanner?->title ?: '100% Original K-Beauty Essentials' }}</h3>
                            <p>{{ $twinLeftBanner?->subtitle ?: 'Your daily dose of authenticity.' }}</p>
                            <span class="btn">{{ $twinLeftBanner?->cta_label ?: 'Shop Beauty' }} <x-icon name="arrow-right" size="16" /></span>
                        </div>
                    </a>
                    <a class="ban {{ $twinRightBanner?->theme ?: 'dark' }} st" href="{{ $twinRightBanner?->cta_url ? url($twinRightBanner->cta_url) : route('shop', ['group' => 'gadget']) }}" style="--sd: .3s">
                        <img class="ban-bg" src="{{ asset($twinRightBanner?->image ?: 'images/banners/twin-right.png') }}" alt="" loading="lazy">
                        <div class="bt">
                            <div class="eyebrow">{{ $twinRightBanner?->eyebrow ?: 'Smart Lifestyle' }}</div>
                            <h3>{{ $twinRightBanner?->title ?: 'Premium Lifestyle & Smart Gadgets' }}</h3>
                            <p>{{ $twinRightBanner?->subtitle ?: 'Upgrade to smarter living.' }}</p>
                            <span class="btn light">{{ $twinRightBanner?->cta_label ?: 'Shop Gadgets' }} <x-icon name="arrow-right" size="16" /></span>
                        </div>
                    </a>
                </div>
            </section>
            @break

        @case('budget')
            {{-- ================= UNDER TK X ================= --}}
            @if ($budgetProducts->isNotEmpty())
            <section class="sec rv" data-reveal aria-labelledby="t-budget" style="padding-bottom: 0">
                <div class="wrap">
                    <div class="phead">
                        <div>
                            <div class="eyebrow">{{ $section->eyebrow ?: 'Small price, real thing' }}</div>
                            <h2 class="stitle" id="t-budget" style="margin-top: 8px">{{ $section->title ?: 'Under '.\App\Models\Product::taka($budgetLimit) }}</h2>
                        </div>
                        <a class="vall" href="{{ route('shop', ['max' => $budgetLimit, 'sort' => 'price_asc']) }}">View all <x-icon name="arrow-right" size="14" /></a>
                    </div>
                    <div class="panel">
                        <ul class="track">
                            @foreach ($budgetProducts as $product)
                                <x-product-card :product="$product" :index="$loop->index" />
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
            @endif
            @break

        @case('routine')
            {{-- ================= SHOP BY ROUTINE (progress bars) ================= --}}
            @php
                $maxCount = max(1, $categories->max('products_count'));
            @endphp
            <section class="sec routine rv" data-reveal aria-labelledby="t-routine">
                <div class="wrap inner">
                    <div class="rimg st" style="--sd: .1s">
                        <img src="{{ asset('images/banners/cat-kbeauty.webp') }}" alt="K-Beauty serum, essence, cream and cleanser" width="1024" height="1024" loading="lazy">
                        <span class="rbadge"><x-icon name="shield" size="18" />100% authentic</span>
                    </div>
                    <div>
                        <div class="eyebrow st" style="--sd: .1s">{{ $section->eyebrow ?: 'Build your routine' }}</div>
                        <h2 class="stitle st" id="t-routine" style="--sd: .2s; margin-top: 10px">{{ $section->title ?: 'Everything Your Skin & Day Needs' }}</h2>
                        <p class="rcopy st" style="--sd: .3s">{{ $section->subtitle ?: 'From morning sunscreen to a charged phone by night, pick a step and see what we have in stock.' }}</p>
                        <ul class="rbars">
                            @foreach ($categories as $category)
                                <li class="st" style="--sd: {{ 0.35 + $loop->index * 0.06 }}s">
                                    <a href="{{ route('category.show', $category) }}">
                                        <span class="rname">{{ $category->name }}</span>
                                        <span class="rnum">{{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}</span>
                                    </a>
                                    <div class="bar"><i style="width: {{ round($category->products_count / $maxCount * 100) }}%; --sd: {{ 0.5 + $loop->index * 0.08 }}s"></i></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
            @break

        @case('concierge')
            {{-- ================= CONCIERGE ================= --}}
            <section class="sec" id="concierge">
                <div class="wrap">
                <div class="conc rv" data-reveal aria-labelledby="t-conc">
                    <div class="cbgimg">
                        <img src="{{ asset($conciergeBanner?->image ?: 'images/banners/concierge-bg-unique.webp') }}" alt="SafeBuy Concierge global direct air-cargo sourcing for authentic K-Beauty and J-Beauty" width="1264" height="848" loading="lazy">
                        <div class="cargo st" style="--sd: .85s">
                            <span class="cgi"><x-icon name="globe" size="16" /></span>
                            <b>Direct<br>Global Air-Cargo</b>
                            <small>Barcode verified &amp; custom-cleared authentic supply chain.</small>
                            <em>100% Anti-fake guarantee</em>
                        </div>
                    </div>
                    <div class="cglow" aria-hidden="true"></div>
                    <div class="inner">
                        <div class="ctext">
                            <div class="cpill st" style="--sd: .1s">
                                <span class="cpi"><x-icon name="globe" size="12" stroke="2.2" /></span>
                                <b>SafeBuy Concierge</b><i></i><span>{{ $conciergeBanner?->eyebrow ?: 'Premium Product Sourcing Service' }}</span>
                            </div>
                            <h2 class="ctitle st" id="t-conc" style="--sd: .25s">Hunting for a Specific<br><span class="kb">K-Beauty</span> or <span class="jb">J-Beauty</span><br><span class="scr">Secret?</span></h2>
                            <p class="st" style="--sd: .4s">{{ $conciergeBanner?->subtitle ?: "Can't find your favorite authentic Korean or Japanese skincare product anywhere in Bangladesh? Stop scrolling and let our global sourcing team fly it directly to you." }}</p>
                            <a class="cbtn st" style="--sd: .55s" href="{{ $conciergeBanner?->cta_url ? url($conciergeBanner->cta_url) : route('concierge') }}">
                                <span class="cbi"><x-icon name="plane" size="16" /></span>
                                {{ $conciergeBanner?->cta_label ?: 'Launch concierge request' }}
                                <x-icon name="arrow-right" size="16" class="cba" />
                            </a>
                            <div class="hubs st" style="--sd: .7s">
                                <div class="hubx">
                                    <span class="flag">
                                        <svg viewBox="0 0 30 20" aria-hidden="true"><rect width="30" height="20" fill="#fff"/><path d="M15 5a5 5 0 0 1 0 10 2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 0 0-5z" fill="#cd2e3a"/><path d="M15 15a5 5 0 0 1 0-10 2.5 2.5 0 0 1 0 5 2.5 2.5 0 0 0 0 5z" fill="#0047a0"/><g stroke="#000" stroke-width="1"><path d="M5 4.5l3 2.5M4 6l3 2.5M23 4.5l-3 2.5M24 6l-3 2.5M5 15.5l3-2.5M4 14l3-2.5M23 15.5l-3-2.5M24 14l-3-2.5"/></g></svg>
                                    </span>
                                    <div><b class="kb">Seoul Hub</b><small>South Korea</small></div>
                                </div>
                                <div class="hubx">
                                    <span class="flag">
                                        <svg viewBox="0 0 30 20" aria-hidden="true"><rect width="30" height="20" fill="#fff"/><circle cx="15" cy="10" r="6" fill="#bc002d"/></svg>
                                    </span>
                                    <div><b class="jb">Tokyo Hub</b><small>Japan</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="cfeats">
                        <li class="st" style="--sd: .9s"><span><x-icon name="shield" size="17" /></span><div><b>100% Authentic</b><small>Only genuine products from verified sources.</small></div></li>
                        <li class="st" style="--sd: 1s"><span><x-icon name="plane" size="17" /></span><div><b>Global Sourcing</b><small>Products shipped directly from Seoul &amp; Tokyo.</small></div></li>
                        <li class="st" style="--sd: 1.1s"><span><x-icon name="box" size="17" /></span><div><b>Safe &amp; Secure</b><small>Barcode verified, custom cleared &amp; trackable.</small></div></li>
                        <li class="st" style="--sd: 1.2s"><span><x-icon name="headset" size="17" /></span><div><b>Concierge Support</b><small>Personal assistance from request to doorstep.</small></div></li>
                    </ul>
                </div>
                </div>
            </section>
            @break

        @case('philosophy')
            {{-- ================= PHILOSOPHY + COMPARE ================= --}}
            <section class="sec phil rv" data-reveal aria-labelledby="t-phil">
                <div class="wrap inner">
                    <div>
                        <div class="eyebrow st" style="--sd: .05s">{{ $section->eyebrow ?: 'Our brand philosophy' }}</div>
                        <h2 class="stitle st" id="t-phil" style="--sd: .15s; margin-top: 10px">{{ $section->title ?: 'Smart Choice, Uncompromised Trust.' }}</h2>
                        @foreach ([
                            ['Safe to Buy', 'We eliminate the fear of counterfeit products. At SafeBuy, every beauty item is sourced through verified channels and comes with a guarantee of authenticity.'],
                            ['Smart to Trust', 'Technology should enhance your life, not complicate it. We handpick gadgets that offer the best performance, reliability, and modern convenience.'],
                            ['Customer First', 'Your satisfaction is our priority. From seamless order tracking to safe doorstep delivery, we ensure a happy and hassle-free shopping experience.'],
                        ] as [$title, $text])
                            <div class="pitem st" style="--sd: {{ 0.3 + $loop->index * 0.15 }}s">
                                <h3><span>◆</span>{{ $title }}</h3>
                                <p>{{ $text }}</p>
                                <div class="bar"><i style="--sd: {{ 0.5 + $loop->index * 0.2 }}s"></i></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="st" style="--sd: .3s">
                        <div class="cmp" data-compare>
                            <div class="layer after">
                                <div class="lbox">
                                    <div class="bigic ok"><x-icon name="shield" size="30" stroke="1.6" /></div>
                                    <h4>The SafeBuy original</h4>
                                    <ul>
                                        @foreach (['Directly imported from South Korea', 'Barcode verified & trackable', 'Sourced through verified channels', 'Concierge support to your door'] as $line)
                                            <li><x-icon name="check" size="16" stroke="2.2" class="ic-ok" />{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="layer before" data-compare-before>
                                <div class="lbox">
                                    <div class="bigic"><x-icon name="shield-x" size="30" stroke="1.6" /></div>
                                    <h4>The counterfeit risk</h4>
                                    <ul>
                                        @foreach (['Unknown source', 'No barcode you can check', 'Unclear what is inside', 'No one to ask after you buy'] as $line)
                                            <li><x-icon name="x" size="16" stroke="2.2" class="ic-bad" />{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <span class="chip l">Counterfeit</span>
                            <span class="chip r">SafeBuy</span>
                            <span class="handle" data-compare-handle><span class="knob"><x-icon name="arrows-lr" size="20" stroke="2" /></span></span>
                            <input type="range" min="0" max="100" step="1" value="50" aria-label="Drag to compare counterfeit and SafeBuy original" data-compare-range>
                        </div>
                        <p class="hint"><x-icon name="arrows-lr" size="16" />Drag the handle to compare</p>
                    </div>
                </div>
            </section>
            @break

        @case('testimonials')
            {{-- ================= TESTIMONIALS ================= --}}
            @php
                $testiList = $testimonials->isNotEmpty() ? $testimonials : collect([
                    (object) ['quote' => 'With the fake Korean cosmetics in the market, I was afraid to buy anything online. But I am 100% satisfied with the sunscreen from SafeBuy. I got the original product by scanning the barcode. Thank you SafeBuy!', 'name' => 'Anika Rahman', 'location' => 'Dhaka — K-Beauty Customer', 'photo' => 'images/testimonials/anika.webp'],
                    (object) ['quote' => 'The quality of their lifestyle gadgets is truly premium. Delivery was very fast and the convenience of paying after receiving the product was great.', 'name' => 'Tanvir Ahmed', 'location' => 'Chittagong — Smart Lifestyle Buyer', 'photo' => 'images/testimonials/tanvir.webp'],
                ]);
            @endphp
            <section class="sec testi rv" data-reveal aria-labelledby="t-testi" style="padding-top: 24px" data-testi>
                <div class="wrap">
                    <div class="shead">
                        <div class="eyebrow">{{ $section->eyebrow ?: 'Testimonials' }}</div>
                        <h2 class="stitle" id="t-testi" style="margin-top: 8px">{{ $section->title ?: 'What Our Clients Say' }}</h2>
                        <x-squiggle />
                    </div>
                    <svg class="qmark" viewBox="0 0 64 48" aria-hidden="true"><path d="M0 48V28C0 12 8 3 24 0l3 6C17 9 13 15 13 22h11v26zm37 0V28c0-16 8-25 24-28l3 6c-10 3-14 9-14 16h11v26z" fill="currentColor"/></svg>
                    @foreach ($testiList as $t)
                        <div class="tq @if ($loop->first) on @endif" data-tq>
                            <blockquote>“{{ $t->quote }}”</blockquote>
                            @if ($t->photo)<img class="av" src="{{ asset($t->photo) }}" alt="" width="64" height="64" loading="lazy">@endif
                            <div class="who">{{ $t->name }}</div>
                            @if ($t->location)<div class="where">{{ $t->location }}</div>@endif
                        </div>
                    @endforeach
                    @if ($testiList->count() > 1)
                        <div class="tdots">
                            @for ($i = 0; $i < $testiList->count(); $i++)
                                <button class="tdot @if ($i === 0) on @endif" type="button" aria-label="Show testimonial {{ $i + 1 }}" data-tdot="{{ $i }}"><i></i></button>
                            @endfor
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('brands_marquee')
            {{-- ================= BRANDS MARQUEE ================= --}}
            @php
                $brandLogos = $partnerBrands->isNotEmpty()
                    ? $partnerBrands->map(fn ($b) => ['src' => $b->logo, 'name' => $b->name])->all()
                    : [
                        ['src' => 'images/brands/cosrx.webp', 'name' => 'COSRX'],
                        ['src' => 'images/brands/beauty-of-joseon.webp', 'name' => 'Beauty of Joseon'],
                        ['src' => 'images/brands/anua.webp', 'name' => 'Anua'],
                        ['src' => 'images/brands/dr-althea.webp', 'name' => 'Dr.Althea'],
                        ['src' => 'images/brands/dabo.webp', 'name' => 'Dabo'],
                    ];
                $gadgets = ['Smart Watch', 'Earbuds', 'Mobile Tech', 'Audio', 'Smart Devices'];
            @endphp
            <section class="mq" aria-label="Trusted brand partners">
                <div class="mlabel">{{ $section->title ?: 'Trusted brand partners' }}</div>
                <div class="mrow logos">
                    @for ($r = 0; $r < 4; $r++)
                        @foreach ($brandLogos as $b)
                            <span @if ($r > 0) aria-hidden="true" @endif><img src="{{ asset($b['src']) }}" alt="{{ $r === 0 ? $b['name'] : '' }}" height="64" loading="lazy"></span>
                        @endforeach
                    @endfor
                </div>
                <div class="mrow rev">
                    @for ($r = 0; $r < 4; $r++)
                        @foreach ($gadgets as $g)
                            <span @if ($r > 0) aria-hidden="true" @endif>{{ $g }}<em>✦</em></span>
                        @endforeach
                    @endfor
                </div>
            </section>
            @break

    @endswitch
@endforeach

@endsection
