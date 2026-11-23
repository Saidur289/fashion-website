{{-- Gallery: main image (click = lightbox) + thumbnails. In Quick View it is a slider with dots. --}}
@php
    $imgs = $product->gallery();
@endphp
<div class="gal {{ ($quick ?? false) ? 'is-quick' : '' }}" data-gallery>
    <div class="gmain">
        <div class="tags">
            @if ($product->is_featured)<span class="tag feat">Featured</span>@endif
            @if ($product->discount_percent && ! $product->isVariable())<span class="tag off">-{{ $product->discount_percent }}%</span>@endif
        </div>
        @foreach ($imgs as $i => $src)
            <button type="button" class="gslide {{ $i === 0 ? 'on' : '' }}" data-gslide="{{ $i }}" data-src="{{ asset($src) }}" aria-label="Enlarge image {{ $i + 1 }}" @if ($i) tabindex="-1" @endif>
                <img src="{{ asset($src) }}" alt="{{ $i === 0 ? $product->name : '' }}" width="800" height="800" decoding="async" @if ($i) loading="lazy" @else fetchpriority="high" @endif>
            </button>
        @endforeach
        @if (count($imgs) > 1)
            <button type="button" class="garr l" aria-label="Previous image" data-gstep="-1"><x-icon name="arrow-left" size="16" /></button>
            <button type="button" class="garr r" aria-label="Next image" data-gstep="1"><x-icon name="arrow-right" size="16" /></button>
        @endif
    </div>
    @if (count($imgs) > 1)
        @if ($quick ?? false)
            <div class="gdots">
                @foreach ($imgs as $i => $src)<button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-gthumb="{{ $i }}" aria-label="Image {{ $i + 1 }}"><i></i></button>@endforeach
            </div>
        @else
            <div class="gthumbs">
                @foreach ($imgs as $i => $src)
                    <button type="button" class="{{ $i === 0 ? 'on' : '' }}" data-gthumb="{{ $i }}" aria-label="Show image {{ $i + 1 }}"><img src="{{ asset($src) }}" alt="" width="120" height="120" loading="lazy"></button>
                @endforeach
            </div>
        @endif
    @endif
</div>
