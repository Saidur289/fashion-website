@extends('layouts.app')

@section('title', 'Contact Us — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Get in touch with SafeBuy for order help, skincare advice, or product authenticity questions.')

@section('content')
@include('partials.page-head', [
    'title' => 'Contact Us',
    'crumbs' => [['Contact', null]],
])

<div class="wrap cp-wrap">
    <div class="cp-split">
        <div data-motion="slide-left">
            <div class="cp-card">
                <h2 style="margin-top:0;color:var(--blue)">Reach SafeBuy</h2>
                <p class="cp-muted">Have a question about an order, delivery charge, or product authenticity? Send us a message and we will get back to you promptly.</p>

                <ul class="cp-info-list">
                    <li>
                        <strong>Store Location</strong>
                        <span>{{ config('safebuy.address', 'Jhenaidah, Bangladesh') }}</span>
                    </li>
                    @if (config('safebuy.phone'))
                        <li>
                            <strong>Phone / Hotline</strong>
                            <a href="tel:{{ preg_replace('/\s+/', '', config('safebuy.phone')) }}">{{ config('safebuy.phone') }}</a>
                        </li>
                    @endif
                    <li>
                        <strong>Email</strong>
                        <a href="mailto:{{ config('safebuy.email') }}">{{ config('safebuy.email') }}</a>
                    </li>
                    @if (config('safebuy.hours'))
                        <li>
                            <strong>Open Hours</strong>
                            <span>{{ config('safebuy.hours') }}</span>
                        </li>
                    @endif
                </ul>

                <div class="cp-map">
                    <iframe
                        title="SafeBuy Location Map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://maps.google.com/maps?q={{ urlencode(config('safebuy.address', 'Jhenaidah, Bangladesh')) }}&output=embed"
                    ></iframe>
                </div>
                <p style="margin:10px 0 0;font-size:13px"><a class="tlink" href="https://maps.google.com/?q={{ urlencode(config('safebuy.address', 'Jhenaidah, Bangladesh')) }}" target="_blank" rel="noopener">Open location in Google Maps <x-icon name="arrow-right" size="13" /></a></p>
            </div>
        </div>

        <div data-motion="slide-right">
            <form class="cp-form" action="{{ route('contact.store') }}" method="post">
                @csrf
                <h2 style="margin-top:0;color:var(--blue)">Send a Message</h2>

                @if (session('status'))
                    <div class="cp-alert ok">{{ session('status') }}</div>
                @endif

                <div class="cp-grid2">
                    <label class="cp-fld">
                        <span>Your Name <i>*</i></span>
                        <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required>
                        @error('name')<em>{{ $message }}</em>@enderror
                    </label>
                    <label class="cp-fld">
                        <span>Mobile Number</span>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="01XXXXXXXXX">
                        @error('phone')<em>{{ $message }}</em>@enderror
                    </label>
                </div>

                <div class="cp-grid2">
                    <label class="cp-fld">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="you@example.com">
                        @error('email')<em>{{ $message }}</em>@enderror
                    </label>
                    <label class="cp-fld">
                        <span>Subject</span>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Order inquiry, skincare question…">
                        @error('subject')<em>{{ $message }}</em>@enderror
                    </label>
                </div>

                <label class="cp-fld">
                    <span>Message <i>*</i></span>
                    <textarea name="message" rows="5" required placeholder="Write your question or order number here…">{{ old('message') }}</textarea>
                    @error('message')<em>{{ $message }}</em>@enderror
                </label>

                <button class="btn orange" type="submit">Send Message <x-icon name="arrow-right" size="15" /></button>
            </form>
        </div>
    </div>
</div>
@endsection
