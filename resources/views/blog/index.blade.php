@extends('layouts.app')

@section('title', 'Blog & Guides — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', 'Korean skincare tips, product authenticity guides, and smart gadget reviews from SafeBuy.')

@section('content')
@include('partials.page-head', [
    'title' => $activeCategory ? $activeCategory->name : ($activeTag ? '#'.$activeTag->name : 'SafeBuy Blog & Guides'),
    'crumbs' => array_values(array_filter([
        ['Blog', route('blog.index')],
        $activeCategory ? [$activeCategory->name, null] : null,
        $activeTag ? ['#'.$activeTag->name, null] : null,
    ])),
])

<div class="wrap cp-wrap">
    <div class="blog-layout">
        <div class="blog-main">
            <div class="blog-toolbar">
                <span class="blog-count">Showing {{ $posts->count() }} of {{ $posts->total() }} articles</span>
                <div class="blog-views">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" @class(['bview', 'on' => $viewMode === 'grid']) aria-label="Grid view">Grid</a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" @class(['bview', 'on' => $viewMode === 'list']) aria-label="List view">List</a>
                </div>
            </div>

            @if ($posts->isEmpty())
                <div class="empty">
                    <h2>No articles found</h2>
                    <p>Try clearing your search or choosing another category.</p>
                    <a class="btn" href="{{ route('blog.index') }}">View all articles</a>
                </div>
            @else
                <div @class(['blog-grid', 'is-list' => $viewMode === 'list']) data-stagger="0.06">
                    @foreach ($posts as $post)
                        <article class="bcard">
                            <a class="bcard-img" href="{{ route('blog.show', $post) }}">
                                <img src="{{ asset($post->image ?: 'images/brand/safebuy-logo.webp') }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
                                @if ($post->category)
                                    <span class="bcard-cat">{{ $post->category->name }}</span>
                                @endif
                            </a>
                            <div class="bcard-body">
                                <div class="bcard-meta">
                                    <span>{{ ($post->published_at ?? $post->created_at)->format('d M Y') }}</span>
                                    <span>·</span>
                                    <span>{{ $post->readingMinutes() }} min read</span>
                                </div>
                                <h2><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h2>
                                <p>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}</p>
                                <a class="tlink" href="{{ route('blog.show', $post) }}">Read article <x-icon name="arrow-right" size="14" /></a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($posts->hasPages())
                    <div class="cp-pager">{{ $posts->links() }}</div>
                @endif
            @endif
        </div>

        <aside class="blog-side" data-stagger="0.07">
            <div class="bside-box">
                <h3>Search Blog</h3>
                <form class="bsearch" action="{{ route('blog.index') }}" method="get">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search skincare, gadgets…">
                    <button class="btn sm orange" type="submit">Go</button>
                </form>
            </div>

            @if ($categories->isNotEmpty())
                <div class="bside-box">
                    <h3>Categories</h3>
                    <ul class="bcat-list">
                        <li><a href="{{ route('blog.index') }}" @class(['on' => ! $activeCategory && ! $activeTag])>All articles</a></li>
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" @class(['on' => $activeCategory?->id === $cat->id])>
                                    <span>{{ $cat->name }}</span>
                                    <small>{{ $cat->posts_count }}</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($tags->isNotEmpty())
                <div class="bside-box">
                    <h3>Popular Tags</h3>
                    <div class="btag-cloud">
                        @foreach ($tags as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" @class(['btag', 'on' => $activeTag?->id === $tag->id])>#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bside-box bside-cta">
                <h3>Looking for a specific item?</h3>
                <p>Ask our SafeBuy Concierge to source authentic Korean beauty or smart gadgets directly for you.</p>
                <a class="btn orange wide" href="{{ route('concierge') }}">Request via Concierge</a>
            </div>
        </aside>
    </div>
</div>
@endsection
