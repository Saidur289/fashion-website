@if ($cart['free_over'] > 0)
    @php $pct = min(100, round(($cart['subtotal'] - $cart['discount']) / $cart['free_over'] * 100)); @endphp
    <div class="freebar">
        <x-icon name="truck" size="22" />
        <div>
            <p>
                @if ($cart['free_left'] > 0)
                    Spend <b>{{ \App\Models\Product::taka($cart['free_left']) }}</b> more to get <b>FREE delivery!</b>
                @else
                    <b>Congratulations!</b> Your order gets <b>FREE delivery.</b>
                @endif
            </p>
            <div class="ship-bar"><i style="width: {{ $pct }}%"></i></div>
        </div>
    </div>
@endif
