{{-- Reviews tab: approved reviews + the form for verified buyers. $reviews, $reviewState: guest|can|done|not_buyer --}}
@php
    $count = $reviews->count();
    $avg = $count ? round($reviews->avg('rating'), 1) : 0;
@endphp
<div class="revs">
    @if (session('review_status'))<p class="alert ok">{{ session('review_status') }}</p>@endif
    @if (session('review_error'))<p class="alert bad">{{ session('review_error') }}</p>@endif

    <div class="rev-sum">
        <div class="rev-avg">
            <b>{{ $count ? number_format($avg, 1) : '–' }}</b>
            <span class="srow big" style="--r: {{ $avg * 20 }}%" aria-hidden="true">★★★★★</span>
            <small>{{ $count }} {{ \Illuminate\Support\Str::plural('review', $count) }}</small>
        </div>
        <ul class="rev-bars" aria-label="Rating breakdown">
            @for ($s = 5; $s >= 1; $s--)
                @php $n = $reviews->where('rating', $s)->count(); @endphp
                <li><span>{{ $s }} ★</span><div class="bar"><i style="width: {{ $count ? round($n / $count * 100) : 0 }}%; transform: none"></i></div><em>{{ $n }}</em></li>
            @endfor
        </ul>
    </div>

    @if ($count)
        @php $photos = $reviews->flatMap->images; @endphp
        @if ($photos->isNotEmpty())
            <div class="rev-photos" id="review-photos">
                <h3>{{ $photos->count() }} {{ \Illuminate\Support\Str::plural('photo', $photos->count()) }} from customers</h3>
                <div>@foreach ($photos as $ph)<button type="button" data-lightbox-src="{{ asset($ph->path) }}" aria-label="Open customer photo"><img src="{{ asset($ph->path) }}" alt="" width="90" height="90" loading="lazy"></button>@endforeach</div>
            </div>
        @endif
        <ul class="rev-list">
            @foreach ($reviews as $r)
                <li>
                    <div class="rev-h">
                        <span class="acc-av sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($r->authorName(), 0, 1)) }}</span>
                        <div>
                            <b>{{ $r->authorName() }}</b> <span class="verified"><x-icon name="check" size="12" stroke="2.6" /> Verified buyer</span>
                            <div class="srow" style="--r: {{ $r->rating * 20 }}%" aria-label="{{ $r->rating }} out of 5">★★★★★</div>
                        </div>
                        <time datetime="{{ $r->created_at->toDateString() }}">{{ $r->created_at->format('j M Y') }}</time>
                    </div>
                    <p>{{ $r->body }}</p>
                    @if ($r->images->isNotEmpty())
                        <div class="rev-imgs">@foreach ($r->images as $ph)<button type="button" data-lightbox-src="{{ asset($ph->path) }}" aria-label="Open photo"><img src="{{ asset($ph->path) }}" alt="" width="72" height="72" loading="lazy"></button>@endforeach</div>
                    @endif
                    @if ($r->reply)<div class="rev-reply"><b>SafeBuy replied:</b> {{ $r->reply }}</div>@endif
                </li>
            @endforeach
        </ul>
    @else
        <div class="noreview">
            <h3>No reviews yet</h3>
            <p>Reviews come from customers who received this product.</p>
        </div>
    @endif

    <div class="rev-form-wrap">
        @switch($reviewState)
            @case('guest')
                <p class="small"><a class="tlink" href="{{ route('login', ['redirect' => url()->current().'#reviews']) }}">Log in</a> to review a product you bought from us.</p>
                @break
            @case('not_buyer')
                <p class="small">Only customers who received this product can review it. Once your order is delivered, you can review it here.</p>
                @break
            @case('done')
                <p class="small">You have reviewed this product. Thank you!</p>
                @break
            @case('can')
                <form class="rev-form" method="post" action="{{ route('reviews.store', $product) }}" enctype="multipart/form-data" novalidate>
                    @csrf
                    <h3>Write a review</h3>
                    <fieldset class="rate-pick">
                        <legend>Your rating <i>*</i></legend>
                        @for ($s = 5; $s >= 1; $s--)
                            <input type="radio" name="rating" value="{{ $s }}" id="rate{{ $s }}" @checked((int) old('rating') === $s)><label for="rate{{ $s }}" title="{{ $s }} {{ \Illuminate\Support\Str::plural('star', $s) }}">★</label>
                        @endfor
                    </fieldset>
                    @error('rating')<em class="ferr">Please choose a star rating.</em>@enderror
                    <label class="fld"><span>Your review <i>*</i></span><textarea name="body" rows="4" maxlength="2000" required @class(['bad' => $errors->has('body')])>{{ old('body') }}</textarea>@error('body')<em>{{ $message }}</em>@enderror</label>
                    <label class="fld"><span>Photos <small>(optional, up to 3)</small></span><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-max-files="3">@error('photos')<em>{{ $message }}</em>@enderror @error('photos.*')<em>Photos must be JPG, PNG or WebP, up to 4 MB each.</em>@enderror</label>
                    <button class="btn orange" type="submit">Submit review</button>
                </form>
                @break
        @endswitch
    </div>
</div>
