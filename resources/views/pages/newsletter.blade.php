@extends('layouts.app')

@section('title', 'Newsletter — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Subscribe to SafeBuy for exclusive deals, new Korean beauty drops, and fake-product awareness alerts.')

@section('content')
@include('partials.page-head', [
    'title' => 'SafeBuy Newsletter',
    'crumbs' => [['Newsletter', null]],
])

<div class="wrap cp-wrap">
    <div class="cp-nl-card" data-motion="scale-in">
        <span class="eyebrow">Stay in the loop</span>
        <h2 style="margin:8px 0 12px;color:var(--blue);font-family:var(--serif);font-size:34px">Deals, Restocks &amp; Authenticity Alerts</h2>
        <p class="cp-muted" style="max-width:540px;margin:0 auto 24px">Join our community for early access to K-Beauty restocks, member-only coupon codes, and practical tips to spot counterfeit products in Bangladesh.</p>

        @if (session('newsletter_status'))
            <div class="cp-alert ok" style="max-width:520px;margin:0 auto 20px">{{ session('newsletter_status') }}</div>
        @endif

        <form class="cp-verify-box" action="{{ route('newsletter.store') }}" method="post" style="max-width:520px;margin:0 auto">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address…" required>
            <button class="btn orange" type="submit">Subscribe</button>
        </form>
        @error('email')<p style="color:var(--bad);font-size:13px;margin-top:8px">{{ $message }}</p>@enderror
    </div>
</div>
@endsection
