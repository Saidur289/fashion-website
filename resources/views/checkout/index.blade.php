@extends('layouts.app')

@section('title', 'Checkout - SafeBuy')

@section('content')
@php
    use App\Models\Product;
    $bk = config('safebuy.bkash');
    $bkVerb = match ($bk['type']) { 'merchant' => 'Payment', 'agent' => 'Cash Out', default => 'Send Money' };
    $other = old('ship_different');
@endphp
@include('partials.page-head', ['title' => 'Checkout', 'crumbs' => [['Cart', route('cart')], ['Checkout', null]]])

<div class="wrap sec-in">
    @if ($errors->any())
        <div class="alert bad" role="alert" tabindex="-1" data-errors>
            <b>Please fix the following:</b>
            <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="ctoggles">
        @guest
            <details class="ctoggle">
                <summary><x-icon name="user" size="16" /> Returning customer? <span>Click here to log in</span></summary>
                <div class="login-inline">@include('auth._login-form', ['idp' => 'c', 'redirect' => route('checkout')])</div>
            </details>
        @endguest
        <details class="ctoggle" @if ($cart['coupon']) open @endif>
            <summary><x-icon name="card" size="16" /> Have a coupon? <span>Click here to enter your code</span></summary>
            <form class="coupon" data-coupon-form>
                <label class="vh" for="cc2">Coupon code</label>
                <input id="cc2" name="code" placeholder="Coupon code" value="{{ $cart['coupon']['code'] ?? '' }}" autocomplete="off">
                <button class="btn blue sm" type="submit">Apply coupon</button>
            </form>
        </details>
    </div>

    <form class="checkout" method="post" action="{{ route('checkout.store') }}" data-checkout novalidate>
        @csrf
        <section class="bill" aria-labelledby="t-bill">
            <h2 id="t-bill">Delivery details</h2>
            @if ($addresses->count() > 1)
                <label class="fld">
                    <span>Use a saved address</span>
                    <span class="sel full">
                        <select data-saved-address>
                            @foreach ($addresses as $a)
                                <option value="{{ json_encode($a->only(['name', 'phone', 'district', 'area', 'address'])) }}">{{ $a->label ?: 'Address' }} – {{ $a->oneLine() }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" size="14" />
                    </span>
                </label>
            @endif
            <div class="frow two">
                <label class="fld">
                    <span>Full name <i>*</i></span>
                    <input name="name" value="{{ old('name', $prefill['name']) }}" required autocomplete="name" @class(['bad' => $errors->has('name')])>
                    @error('name')<em>{{ $message }}</em>@enderror
                </label>
                <label class="fld">
                    <span>Mobile number <i>*</i></span>
                    <input name="phone" value="{{ old('phone', $prefill['phone']) }}" required inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" @class(['bad' => $errors->has('phone')])>
                    @error('phone')<em>{{ $message }}</em>@enderror
                </label>
            </div>
            <label class="fld">
                <span>Email <small>(optional, for the order receipt)</small></span>
                <input name="email" type="email" value="{{ old('email', $prefill['email']) }}" autocomplete="email" @class(['bad' => $errors->has('email')])>
                @error('email')<em>{{ $message }}</em>@enderror
            </label>
            <div class="frow two">
                <label class="fld">
                    <span>District <i>*</i></span>
                    <span class="sel full">
                        <select name="district" required data-district @class(['bad' => $errors->has('district')])>
                            <option value="">Choose your district…</option>
                            @foreach ($districts as $d)
                                <option @selected(old('district', $cart['district'] ?? $prefill['district']) === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                        <x-icon name="chevron-down" size="14" />
                    </span>
                    @error('district')<em>{{ $message }}</em>@enderror
                </label>
                <label class="fld">
                    <span>Area / Thana</span>
                    <input name="area" value="{{ old('area', $prefill['area']) }}" autocomplete="address-level3">
                </label>
            </div>
            <label class="fld">
                <span>Full address <i>*</i></span>
                <textarea name="address" rows="2" required autocomplete="street-address" placeholder="House, road, village / area" @class(['bad' => $errors->has('address')])>{{ old('address', $prefill['address']) }}</textarea>
                @error('address')<em>{{ $message }}</em>@enderror
            </label>

            <label class="chk inline"><input type="checkbox" name="ship_different" value="1" data-ship-toggle @checked($other)><span>Send to a different address (for a gift)</span></label>
            <div class="shipto" data-shipto @unless ($other) hidden @endunless>
                <div class="frow two">
                    <label class="fld"><span>Receiver's name <i>*</i></span><input name="ship_name" value="{{ old('ship_name') }}" @class(['bad' => $errors->has('ship_name')])>@error('ship_name')<em>{{ $message }}</em>@enderror</label>
                    <label class="fld"><span>Receiver's mobile <i>*</i></span><input name="ship_phone" value="{{ old('ship_phone') }}" inputmode="tel" placeholder="01XXXXXXXXX" @class(['bad' => $errors->has('ship_phone')])>@error('ship_phone')<em>{{ $message }}</em>@enderror</label>
                </div>
                <div class="frow two">
                    <label class="fld">
                        <span>District <i>*</i></span>
                        <span class="sel full">
                            <select name="ship_district" data-district-ship @class(['bad' => $errors->has('ship_district')])>
                                <option value="">Choose…</option>
                                @foreach ($districts as $d)<option @selected(old('ship_district') === $d)>{{ $d }}</option>@endforeach
                            </select>
                            <x-icon name="chevron-down" size="14" />
                        </span>
                        @error('ship_district')<em>{{ $message }}</em>@enderror
                    </label>
                    <label class="fld"><span>Area / Thana</span><input name="ship_area" value="{{ old('ship_area') }}"></label>
                </div>
                <label class="fld"><span>Full address <i>*</i></span><textarea name="ship_address" rows="2" @class(['bad' => $errors->has('ship_address')])>{{ old('ship_address') }}</textarea>@error('ship_address')<em>{{ $message }}</em>@enderror</label>
            </div>

            <label class="fld">
                <span>Order notes <small>(optional)</small></span>
                <textarea name="note" rows="2" placeholder="e.g. call before delivery, best time to deliver">{{ old('note') }}</textarea>
            </label>
        </section>

        <aside class="osum" aria-labelledby="t-order">
            <h2 id="t-order">Your order</h2>
            <div data-checkout-summary>
                @include('checkout._summary')
            </div>

            @if (config('safebuy.shipping.pickup_enabled'))
                <label class="chk inline"><input type="checkbox" name="pickup" value="1" data-pickup @checked(old('pickup', $cart['shipping_method'] === 'pickup'))><span>I will collect it from the shop ({{ config('safebuy.shipping.pickup_address') }})</span></label>
            @endif

            <fieldset class="pays">
                <legend>Payment method</legend>
                @forelse ($payments as $key => $label)
                    <label class="pay-opt">
                        <input type="radio" name="payment_method" value="{{ $key }}" data-pay @checked(old('payment_method', array_key_first($payments)) === $key)>
                        <span class="pay-h">
                            @if ($key === 'cod')<img src="{{ asset('images/pay/cod.webp') }}" alt="" height="22">@else<img src="{{ asset('images/pay/bkash.png') }}" alt="" height="22">@endif
                            {{ $label }}
                        </span>
                    </label>
                    @if ($key === 'cod')
                        <div class="pay-box" data-pay-box="cod">Pay in cash when the parcel arrives. We call you first to confirm the order.</div>
                    @else
                        <div class="pay-box" data-pay-box="bkash">
                            <ol class="steps sm">
                                <li>Open bKash and choose <b>{{ $bkVerb }}</b>.</li>
                                <li>Send <b data-bkash-amount>{{ Product::taka($cart['total']) }}</b> to <b class="bnum">{{ $bk['number'] }}</b> ({{ ucfirst($bk['type']) }}).</li>
                                <li>Enter the number you paid from and the Transaction ID below.</li>
                            </ol>
                            <div class="frow two">
                                <label class="fld"><span>Your bKash number <i>*</i></span><input name="bkash_sender" value="{{ old('bkash_sender') }}" inputmode="tel" placeholder="01XXXXXXXXX" @class(['bad' => $errors->has('bkash_sender')])>@error('bkash_sender')<em>{{ $message }}</em>@enderror</label>
                                <label class="fld"><span>Transaction ID <i>*</i></span><input name="bkash_trx_id" value="{{ old('bkash_trx_id') }}" placeholder="e.g. 9KD4AB12XY" autocomplete="off" @class(['bad' => $errors->has('bkash_trx_id')])>@error('bkash_trx_id')<em>{{ $message }}</em>@enderror</label>
                            </div>
                            <p class="small">We check the payment before sending your order.</p>
                        </div>
                    @endif
                @empty
                    <p class="alert bad">No payment method is switched on yet.</p>
                @endforelse
            </fieldset>

            <label class="chk inline terms">
                <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>
                <span>I have read and agree to the website <a href="{{ route('page.terms') }}" target="_blank" rel="noopener">terms and conditions</a> and <a href="{{ route('page.privacy') }}" target="_blank" rel="noopener">privacy policy</a>. <i>*</i></span>
            </label>
            @error('terms')<em class="ferr">{{ $message }}</em>@enderror

            <button class="btn orange wide big" type="submit" data-place-order><x-icon name="check" size="18" /> Place order</button>
            <p class="secure"><x-icon name="shield" size="16" /> Your details are only used to deliver this order.</p>
        </aside>
    </form>
</div>
@endsection
