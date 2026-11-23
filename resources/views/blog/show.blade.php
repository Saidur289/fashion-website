@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title).' — '.config('safebuy.shop_name', 'SafeBuy'))
@section('description', $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 155)))

@section('content')
@include('partials.page-head', [
    'title' => $post->title,
    'crumbs' => array_values(array_filter([
        ['Blog', route('blog.index')],
        $post->category ? [$post->category->name, route('blog.index', ['category' => $post->category->slug])] : null,
        [\Illuminate\Support\Str::limit($post->title, 42), null],
    ])),
])

<article class="wrap cp-wrap blog-article">
    <div class="bpost-meta">
        @if ($post->category)
            <a class="bpost-badge" href="{{ route('blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
        @endif
        <span>{{ ($post->published_at ?? $post->created_at)->format('d M Y') }}</span>
        <span>·</span>
        <span>{{ $post->readingMinutes() }} min read</span>
        @if ($post->author)
            <span>·</span>
            <span>By {{ $post->author->name }}</span>
        @endif
    </div>

    @if ($post->image)
        <figure class="bpost-hero" data-motion="scale-in">
            <img src="{{ asset($post->image) }}" alt="{{ $post->title }}" decoding="async" fetchpriority="high">
        </figure>
    @endif

    <div class="bpost-content prose" data-motion="fade-up">
        {!! \Illuminate\Support\Str::markdown($post->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
    </div>

    <div class="bpost-foot">
        @if ($post->tags->isNotEmpty())
            <div class="btag-cloud">
                @foreach ($post->tags as $tag)
                    <a class="btag" href="{{ route('blog.index', ['tag' => $tag->slug]) }}">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif
        <div class="bshare">
            <span>Share:</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">Facebook</a>
            <a href="https://wa.me/?text={{ urlencode($post->title.' — '.url()->current()) }}" target="_blank" rel="noopener">WhatsApp</a>
        </div>
    </div>

    @if ($post->products->isNotEmpty())
        <section class="bpost-products" aria-labelledby="t-mentioned">
            <h2 class="stitle sm" id="t-mentioned">Featured in this article</h2>
            <div class="panel">
                <ul class="track">
                    @foreach ($post->products as $prod)
                        <x-product-card :product="$prod" :index="$loop->index" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="bpost-related" aria-labelledby="t-rel-posts">
            <h2 class="stitle sm" id="t-rel-posts">Related articles</h2>
            <div class="blog-grid" data-stagger="0.07">
                @foreach ($related as $r)
                    <article class="bcard">
                        <a class="bcard-img" href="{{ route('blog.show', $r) }}">
                            <img src="{{ asset($r->image ?: 'images/brand/safebuy-logo.webp') }}" alt="{{ $r->title }}" loading="lazy" decoding="async">
                        </a>
                        <div class="bcard-body">
                            <div class="bcard-meta"><span>{{ ($r->published_at ?? $r->created_at)->format('d M Y') }}</span></div>
                            <h2><a href="{{ route('blog.show', $r) }}">{{ $r->title }}</a></h2>
                            <a class="tlink" href="{{ route('blog.show', $r) }}">Read more <x-icon name="arrow-right" size="14" /></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bpost-comments" id="comments" aria-labelledby="t-comments">
        <h2 class="stitle sm" id="t-comments">Comments ({{ $post->approvedComments->count() }})</h2>

        @if (session('comment_status'))
            <div class="cp-alert ok">{{ session('comment_status') }}</div>
        @endif

        @if ($post->approvedComments->isNotEmpty())
            <div class="bcom-list">
                @foreach ($post->approvedComments as $c)
                    <div class="bcom-item">
                        <div class="bcom-head">
                            <strong>{{ $c->name }}</strong>
                            <small>{{ $c->created_at->format('d M Y') }}</small>
                        </div>
                        <p>{{ $c->body }}</p>
                        @if ($c->reply)
                            <div class="bcom-reply">
                                <strong>SafeBuy Reply:</strong> {{ $c->reply }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="cp-muted">No comments yet. Be the first to share a thought or question!</p>
        @endif

        <form class="cp-form" action="{{ route('blog.comments.store', $post) }}" method="post">
            @csrf
            <h3>Leave a comment</h3>
            <div class="cp-grid2">
                @guest
                    <label class="cp-fld">
                        <span>Your name <i>*</i></span>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')<em>{{ $message }}</em>@enderror
                    </label>
                    <label class="cp-fld">
                        <span>Email (optional, never shown)</span>
                        <input type="email" name="email" value="{{ old('email') }}">
                        @error('email')<em>{{ $message }}</em>@enderror
                    </label>
                @endguest
            </div>
            <label class="cp-fld">
                <span>Comment <i>*</i></span>
                <textarea name="body" rows="4" required>{{ old('body') }}</textarea>
                @error('body')<em>{{ $message }}</em>@enderror
            </label>
            <button class="btn orange" type="submit">Post comment</button>
        </form>
    </section>
</article>
@endsection
