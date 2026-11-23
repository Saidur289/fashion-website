{{-- Totals table shared by the cart page and checkout. $editable: show delivery choices. --}}
@php use App\Models\Product; @endphp
<dl class="tlines">
    <div><dt>Subtotal</dt><dd>{{ Product::taka($cart['subtotal']) }}</dd></div>
    @if ($cart['coupon'])
        <div class="disc">
            <dt>Coupon <b>{{ $cart['coupon']['code'] }}</b> <button type="button" class="linkbtn" data-coupon-remove>Remove</button></dt>
            <dd>− {{ Product::taka($cart['discount']) }}</dd>
        </div>
    @endif
    <div class="ship-line">
        <dt>Delivery</dt>
        <dd>
            @if ($cart['shipping'] === 0 && $cart['shipping_method'] !== 'pickup')
                <b class="free">FREE</b>
            @else
                {{ Product::taka($cart['shipping']) }}
            @endif
        </dd>
    </div>
    @if ($editable ?? false)
        <div class="ship-opts">
            <fieldset>
                <legend class="vh">Delivery area</legend>
                @foreach ($cart['shipping_options'] as $key => $opt)
                    <label class="radio">
                        <input type="radio" name="ship_method" value="{{ $key }}" data-ship-method @checked($cart['shipping_method'] === $key)>
                        <span>{{ $opt['label'] }}: <b>{{ $opt['price'] ? Product::taka($opt['price']) : 'Free' }}</b>@if (! empty($opt['note']))<small>{{ $opt['note'] }}</small>@endif</span>
                    </label>
                @endforeach
            </fieldset>
            <label class="calc">
                <span>Calculate by district</span>
                <span class="sel">
                    <select data-ship-district aria-label="Delivery district">
                        <option value="">Choose your district…</option>
                        @foreach (config('districts') as $d)
                            <option @selected($cart['district'] === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" size="14" />
                </span>
            </label>
        </div>
    @endif
    <div class="grand"><dt>Total</dt><dd>{{ Product::taka($cart['total']) }}</dd></div>
</dl>
