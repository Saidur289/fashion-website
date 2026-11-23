@extends('layouts.app')

@section('title', 'How to Verify Product Authenticity — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Check SafeBuy verified products by SKU or name and learn how to spot counterfeit K-Beauty & smart gadgets.')

@section('content')
@include('partials.page-head', [
    'title' => 'Verify Product Authenticity',
    'crumbs' => [['Verify Product', null]],
])

<div class="wrap cp-wrap">
    <p class="cp-lead">SafeBuy only sells 100% original Korean/Japanese beauty and genuine smart gadgets. Enter a SafeBuy SKU, product name, or brand below to verify it in our official catalog.</p>

    <form class="cp-verify-box" action="{{ route('verify-product') }}" method="get">
        <input type="search" name="q" value="{{ $q }}" placeholder="Enter SKU (e.g. SB-…), product name, or brand…" required>
        <button class="btn orange" type="submit">Verify Now</button>
    </form>

    @if ($q !== '')
        <section class="cp-sec">
            @if ($matches->isNotEmpty())
                <div class="cp-alert ok">Found {{ $matches->count() }} verified SafeBuy catalog {{ \Illuminate\Support\Str::plural('item', $matches->count()) }} matching “{{ $q }}”.</div>
                <div class="verify-grid" data-stagger="0.06">
                    @foreach ($matches as $p)
                        <a class="verify-card" href="{{ route('product.show', $p) }}">
                            <img src="{{ asset($p->image) }}" alt="{{ $p->name }}" width="80" height="80" loading="lazy" decoding="async">
                            <div>
                                <span class="verify-tag">✓ Official SafeBuy Item</span>
                                <h3>{{ $p->name }}</h3>
                                <small>
                                    {{ $p->brand?->name ? $p->brand->name.' · ' : '' }}
                                    SKU: {{ $p->sku ?: 'N/A' }}
                                    {{ $p->origin ? ' · Origin: '.$p->origin : '' }}
                                </small>
                                <strong>{{ $p->priceLabel() }}</strong>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="cp-alert warn">
                    No active product matched “{{ $q }}” in our catalog. If you purchased this item from SafeBuy or want our team to check a batch code for you, please <a href="{{ route('contact') }}" style="text-decoration:underline;font-weight:600">contact us with a photo</a>.
                </div>
            @endif
        </section>
    @endif

    <section class="cp-sec">
        <h2 class="stitle sm">5-Step Authenticity Checklist</h2>
        <div class="cp-pillars" data-stagger="0.07">
            <div class="cp-pillar">
                <strong>1. Batch &amp; Expiry Code</strong>
                <p>Check that the embossed or laser-printed batch code on the box matches the bottle or tube inside.</p>
            </div>
            <div class="cp-pillar">
                <strong>2. Brand Hologram / QR</strong>
                <p>Scan HiddenTag, official brand QR codes, or scratch-to-verify serial stickers when provided by the brand.</p>
            </div>
            <div class="cp-pillar">
                <strong>3. Factory Seal &amp; Print</strong>
                <p>Original Korean packaging features crisp fonts, clean alignment, and intact factory tamper seals.</p>
            </div>
            <div class="cp-pillar">
                <strong>4. SafeBuy Invoice</strong>
                <p>Every genuine SafeBuy parcel arrives with an official order number (`SB-…`) trackable on our website.</p>
            </div>
        </div>
    </section>
</div>
@endsection
