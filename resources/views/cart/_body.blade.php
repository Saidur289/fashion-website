{{-- Cart page body (Hara layout); re-rendered by safebuy.js after every change. $cart = Cart::summary() --}}
@php use App\Models\Product; @endphp

@if (session('note'))<p class="alert">{{ session('note') }}</p>@endif
@foreach ($cart['notes'] as $note)<p class="alert">{{ $note }}</p>@endforeach

@if (! $cart['items'])
    <div class="empty">
        <x-icon name="bag" size="40" />
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet.</p>
        <a class="btn orange" href="{{ route('shop') }}">Start shopping <x-icon name="arrow-right" size="16" /></a>
    </div>
@else
    <div class="cart-grid">
        <table class="ctable">
            <thead>
                <tr><th scope="col">Product</th><th scope="col">Price</th><th scope="col">Qty</th><th scope="col">Total</th><th scope="col"><span class="vh">Remove</span></th></tr>
            </thead>
            <tbody>
                @foreach ($cart['items'] as $item)
                    <tr>
                        <td class="cprod">
                            <a href="{{ $item['url'] }}"><img src="{{ $item['image'] }}" alt="" width="84" height="84"></a>
                            <div>
                                <a href="{{ $item['url'] }}" class="cname-l">{{ $item['name'] }}@if ($item['variant']) – {{ $item['variant'] }}@endif</a>
                                @if ($item['max'] !== null && $item['max'] <= 5)<span class="clow">Only {{ $item['max'] }} left</span>@endif
                            </div>
                        </td>
                        <td data-label="Price">{{ Product::taka($item['price']) }}</td>
                        <td data-label="Qty">
                            <div class="qty">
                                <button type="button" aria-label="Decrease quantity" data-cq="-1" data-key="{{ $item['key'] }}"><x-icon name="minus" size="14" /></button>
                                <input type="number" min="0" @if ($item['max'] !== null) max="{{ $item['max'] }}" @endif value="{{ $item['qty'] }}" aria-label="Quantity of {{ $item['name'] }}" data-cart-qty="{{ $item['key'] }}" data-was="{{ $item['qty'] }}">
                                <button type="button" aria-label="Increase quantity" data-cq="1" data-key="{{ $item['key'] }}" @disabled($item['max'] !== null && $item['qty'] >= $item['max'])><x-icon name="plus" size="14" /></button>
                            </div>
                        </td>
                        <td data-label="Total" class="ctotal">{{ Product::taka($item['line']) }}</td>
                        <td><button class="mc-del" type="button" data-del="{{ $item['key'] }}" aria-label="Remove {{ $item['name'] }}"><x-icon name="trash" size="17" /></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-actions">
            <a class="btn blue" href="{{ route('shop') }}"><x-icon name="arrow-left" size="16" /> Continue shopping</a>
            <button class="btn ghost" type="button" data-cart-update disabled>Update cart</button>
        </div>

        @if ($cart['free_over'] > 0)
            @php $pct = min(100, (int) round(($cart['subtotal'] - $cart['discount']) / $cart['free_over'] * 100)); @endphp
            <div class="cart-free">
                <div class="ship-bar"><i style="width: {{ max($pct, 6) }}%"><em>{{ $pct }}%</em></i></div>
                <p>
                    @if ($cart['free_left'] > 0)
                        Spend <b>{{ Product::taka($cart['free_left']) }}</b> more to reach <b>FREE DELIVERY!</b><br>
                        <small>Free delivery on orders of {{ Product::taka($cart['free_over']) }} or more.</small>
                    @else
                        <b>Congratulations!</b> Your order gets <b>FREE delivery.</b>
                    @endif
                </p>
            </div>
        @endif

        <div class="cart-bottom">
            <section aria-labelledby="t-coupon">
                <h2 id="t-coupon">Coupon</h2>
                <form class="coupon" data-coupon-form>
                    <label class="vh" for="cc">Coupon code</label>
                    <input id="cc" name="code" placeholder="Enter coupon code here…" value="{{ $cart['coupon']['code'] ?? '' }}" autocomplete="off">
                    <button class="btn blue sm" type="submit">Apply coupon</button>
                </form>
            </section>
            <section class="totals" aria-labelledby="t-totals">
                <h2 id="t-totals">Cart totals</h2>
                @include('cart._totals', ['cart' => $cart, 'editable' => true])
            </section>
            <div class="cart-proceed">
                <a class="btn orange wide big" href="{{ route('checkout') }}">Proceed to checkout <x-icon name="arrow-right" size="16" /></a>
                <p class="secure"><x-icon name="shield" size="16" /> 100% authentic products · Cash on delivery</p>
            </div>
        </div>
    </div>
@endif
