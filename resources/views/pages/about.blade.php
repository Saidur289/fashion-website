@extends('layouts.app')

@section('title', ($page?->meta_title ?: 'About Us').' — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', $page?->meta_description ?: 'Learn how SafeBuy brings 100% authentic Korean skincare and smart gadgets to Bangladesh.')

@section('content')
@include('partials.page-head', [
    'title' => $page?->title ?: 'About SafeBuy',
    'crumbs' => [['About Us', null]],
])

<div class="wrap cp-wrap">
    @if ($page?->subtitle)
        <p class="cp-lead">{{ $page->subtitle }}</p>
    @endif

    <div class="cp-pillars" data-stagger="0.07">
        <div class="cp-pillar">
            <strong>100% Authentic</strong>
            <p>Batch-verified Korean &amp; Japanese beauty plus tested smart gadgets — never counterfeits.</p>
        </div>
        <div class="cp-pillar">
            <strong>All 64 Districts</strong>
            <p>Reliable nationwide delivery with Cash on Delivery and verified bKash checkout.</p>
        </div>
        <div class="cp-pillar">
            <strong>SafeBuy Concierge</strong>
            <p>Can’t find a specific product? Request it and our team sources it directly for you.</p>
        </div>
        <div class="cp-pillar">
            <strong>Honest Support</strong>
            <p>Real humans helping with skin-type advice, product verification, and easy returns.</p>
        </div>
    </div>

    @if ($page)
        <div class="cp-card prose" data-motion="fade-up">
            {!! \Illuminate\Support\Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </div>
    @endif

    @if ($team->isNotEmpty())
        <section class="cp-sec">
            <div class="phead">
                <h2 class="stitle sm">Meet Our Team</h2>
                <a class="vall" href="{{ route('team') }}">View full team <x-icon name="arrow-right" size="14" /></a>
            </div>
            <div class="team-grid" data-stagger="0.07">
                @foreach ($team as $member)
                    <div class="team-card">
                        <div class="team-avatar">
                            @if ($member->photo)
                                <img src="{{ asset($member->photo) }}" alt="{{ $member->name }}" loading="lazy" decoding="async">
                            @else
                                <span>{{ strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <h3>{{ $member->name }}</h3>
                        <small>{{ $member->role }}</small>
                        @if ($member->bio)<p>{{ $member->bio }}</p>@endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="cp-banner">
        <div>
            <h2>Ready to shop with confidence?</h2>
            <p>Explore our verified K-Beauty &amp; smart gadgets collection or ask our Concierge.</p>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a class="btn orange" href="{{ route('shop') }}">Browse Shop</a>
            <a class="btn light" href="{{ route('concierge') }}">Ask Concierge</a>
        </div>
    </div>
</div>
@endsection
