{{--
    Inner-page banner, Hara style: photo on the right, title and breadcrumb on the left.
    $crumbs = [[label, url|null], ...]; optional $banner = image path for this page.
--}}
<section class="phero">
    <img class="phero-bg" src="{{ asset($banner ?? 'images/banners/twin-left.png') }}" alt="" width="1024" height="576">
    <div class="wrap">
        <h1 class="ptitle-in" data-shop-title>{{ $title }}</h1>
        <nav class="crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            @foreach ($crumbs as [$label, $href])
                <span class="dash" aria-hidden="true"></span>
                @if ($href && ! $loop->last)<a href="{{ $href }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif
            @endforeach
        </nav>
    </div>
    <x-leaf style="right: 4%; top: 14%; width: 46px; --r: 30deg; --d: .2s" />
    <x-leaf style="right: 9%; bottom: 10%; width: 34px; --r: -40deg; --d: .4s" />
</section>
