@extends('layouts.app')

@section('title', 'Frequently Asked Questions — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Answers about SafeBuy delivery, Cash on Delivery, bKash payments, returns, and product authenticity.')

@section('content')
@include('partials.page-head', [
    'title' => 'Frequently Asked Questions',
    'crumbs' => [['FAQ', null]],
])

<div class="wrap cp-wrap">
    <div class="faq-top">
        <div class="faq-pills">
            <a href="{{ route('faq') }}" @class(['fpill', 'on' => $category === ''])>All Topics</a>
            @foreach (\App\Models\Faq::CATEGORIES as $k => $label)
                <a href="{{ route('faq', ['category' => $k]) }}" @class(['fpill', 'on' => $category === $k])>{{ $label }}</a>
            @endforeach
        </div>
        <form class="bsearch" action="{{ route('faq') }}" method="get">
            @if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Search questions…">
            <button class="btn sm orange" type="submit">Search</button>
        </form>
    </div>

    @if ($grouped->isEmpty())
        <div class="empty">
            <h2>No matching questions</h2>
            <p>Still have a question? Send us a message directly.</p>
            <a class="btn orange" href="{{ route('contact') }}">Contact Us</a>
        </div>
    @else
        @foreach ($grouped as $catKey => $items)
            <section class="faq-group" data-motion="fade-up">
                <h2 class="faq-heading">{{ \App\Models\Faq::CATEGORIES[$catKey] ?? ucfirst($catKey) }}</h2>
                <div class="faq-list" data-stagger="0.05">
                    @foreach ($items as $faq)
                        <details class="faq-item" @if ($loop->parent->first && $loop->first) open @endif>
                            <summary>{{ $faq->question }}</summary>
                            <div class="faq-ans">{{ $faq->answer }}</div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    <div class="cp-banner" style="margin-top:36px">
        <div>
            <h2>Still have a question?</h2>
            <p>Our support team is happy to help with orders, skincare advice, or product verification.</p>
        </div>
        <a class="btn orange" href="{{ route('contact') }}">Contact Support</a>
    </div>
</div>
@endsection
