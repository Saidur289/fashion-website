@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title).' — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', $page->meta_description ?: ($page->subtitle ?: $page->title))

@section('content')
@include('partials.page-head', [
    'title' => $page->title,
    'crumbs' => [[$page->title, null]],
])

<div class="wrap cp-wrap">
    @if ($page->subtitle)
        <p class="cp-lead">{{ $page->subtitle }}</p>
    @endif

    <article class="cp-card prose" data-motion="fade-up">
        {!! \Illuminate\Support\Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
    </article>

    <div class="cp-policy-links">
        <a href="{{ route('page.shipping-returns') }}">Shipping &amp; Returns</a>
        <a href="{{ route('page.privacy') }}">Privacy Policy</a>
        <a href="{{ route('page.terms') }}">Terms &amp; Conditions</a>
        <a href="{{ route('faq') }}">FAQs</a>
        <a href="{{ route('contact') }}">Contact Us</a>
    </div>
</div>
@endsection
