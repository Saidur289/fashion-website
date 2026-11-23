@extends('layouts.app')

@section('title', 'SafeBuy Concierge — Request Any Original Product')
@section('description', 'Can’t find a specific Korean skincare item or smart gadget? Request it through SafeBuy Concierge and we will source it for you.')

@section('content')
@include('partials.page-head', [
    'title' => 'SafeBuy Concierge',
    'crumbs' => [['Concierge', null]],
])

<div class="wrap cp-wrap">
    <p class="cp-lead">Looking for a specific Korean serum, Japanese sunscreen, or smart gadget not listed in our shop yet? Tell us what you need — we verify authenticity, source it directly, and quote a fair Taka price.</p>

    <div class="cp-pillars" data-stagger="0.07" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
        <div class="cp-pillar">
            <strong>1. Share the Product</strong>
            <p>Give us the product name, brand, link, or upload a screenshot/photo.</p>
        </div>
        <div class="cp-pillar">
            <strong>2. We Verify &amp; Quote</strong>
            <p>Our sourcing team checks authorized suppliers and calls/messages you with the price &amp; ETA.</p>
        </div>
        <div class="cp-pillar">
            <strong>3. Safe Delivery</strong>
            <p>Once confirmed, we deliver your 100% original item right to your doorstep.</p>
        </div>
    </div>

    <form class="cp-form" data-motion="fade-up" action="{{ route('concierge.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <h2 style="margin-top:0;color:var(--blue)">Request a Product</h2>

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
                <span>Mobile Number <i>*</i></span>
                <input type="text" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="01XXXXXXXXX" required>
                @error('phone')<em>{{ $message }}</em>@enderror
            </label>
        </div>

        <div class="cp-grid2">
            <label class="cp-fld">
                <span>Product Name <i>*</i></span>
                <input type="text" name="product_name" value="{{ old('product_name') }}" placeholder="e.g. Beauty of Joseon Relief Sun 50ml" required>
                @error('product_name')<em>{{ $message }}</em>@enderror
            </label>
            <label class="cp-fld">
                <span>Brand (optional)</span>
                <input type="text" name="brand" value="{{ old('brand') }}" placeholder="COSRX, Anua, Anker…">
                @error('brand')<em>{{ $message }}</em>@enderror
            </label>
        </div>

        <div class="cp-grid2">
            <label class="cp-fld">
                <span>Reference Link (optional)</span>
                <input type="url" name="reference_url" value="{{ old('reference_url') }}" placeholder="https://…">
                @error('reference_url')<em>{{ $message }}</em>@enderror
            </label>
            <label class="cp-fld">
                <span>Target Budget (optional)</span>
                <input type="text" name="budget" value="{{ old('budget') }}" placeholder="e.g. Tk 1,500 – 2,000">
                @error('budget')<em>{{ $message }}</em>@enderror
            </label>
        </div>

        <div class="cp-grid2">
            <label class="cp-fld">
                <span>Email (optional)</span>
                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}">
                @error('email')<em>{{ $message }}</em>@enderror
            </label>
            <label class="cp-fld">
                <span>Reference Photo / Screenshot (optional)</span>
                <input type="file" name="image" accept="image/*">
                @error('image')<em>{{ $message }}</em>@enderror
            </label>
        </div>

        <label class="cp-fld">
            <span>Extra Details (shade, size, quantity, skin concern…)</span>
            <textarea name="notes" rows="4">{{ old('notes') }}</textarea>
            @error('notes')<em>{{ $message }}</em>@enderror
        </label>

        <button class="btn orange" type="submit">Submit Concierge Request <x-icon name="arrow-right" size="15" /></button>
    </form>
</div>
@endsection
