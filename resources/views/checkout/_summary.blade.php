{{-- Order summary on checkout; re-rendered by safebuy.js when quantity or district changes. --}}
@php use App\Models\Product; @endphp
@foreach ($cart['notes'] as $note)<p class="alert">{{ $note }}</p>@endforeach
<ul class="olist">
    @foreach ($cart['items'] as $item)
        <li>
            <img src="{{ $item['image'] }}" alt="" width="58" height="58">
            <div>
                <span class="oname">{{ $item['name'] }}@if ($item['variant']) <small>– {{ $item['variant'] }}</small>@endif</span>
                <div class="qty">
                    <button type="button" aria-label="Decrease quantity" data-cq="-1" data-key="{{ $item['key'] }}" data-cur="{{ $item['qty'] }}"><x-icon name="minus" size="12" /></button>
                    <span>{{ $item['qty'] }}</span>
                    <button type="button" aria-label="Increase quantity" data-cq="1" data-key="{{ $item['key'] }}" data-cur="{{ $item['qty'] }}" @disabled($item['max'] !== null && $item['qty'] >= $item['max'])><x-icon name="plus" size="12" /></button>
                </div>
            </div>
            <b>{{ Product::taka($item['line']) }}</b>
        </li>
    @endforeach
</ul>
@include('cart._totals', ['cart' => $cart, 'editable' => false])
<p class="ship-note">
    @if ($cart['shipping_method'] === 'pickup')
        Collect from the shop — no delivery charge.
    @elseif ($cart['district'])
        Delivery to <b>{{ $cart['district'] }}</b> ({{ \App\Models\Order::SHIPPING[$cart['shipping_method']] }}).
    @else
        Choose your district to see the exact delivery charge.
    @endif
</p>
<span hidden data-total="{{ Product::taka($cart['total']) }}"></span>
