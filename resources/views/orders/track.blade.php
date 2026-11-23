@extends('layouts.app')

@section('title', 'Order tracking - SafeBuy')

@section('content')
@php
    use App\Models\Order;
    $flow = Order::FLOW;
@endphp
@include('partials.page-head', ['title' => 'Order Tracking', 'crumbs' => [['Order tracking', null]]])

<div class="wrap sec-in track-page">
    <form class="trackf" method="get" action="{{ route('order.track') }}">
        <p>Enter your order number and the mobile number you used at checkout.</p>
        <div class="frow two">
            <label class="fld"><span>Order number</span><input name="number" value="{{ $number ?: '' }}" placeholder="SB-261123-0001" required></label>
            <label class="fld"><span>Mobile number</span><input name="phone" value="{{ request('phone') }}" inputmode="tel" placeholder="01XXXXXXXXX" required></label>
        </div>
        <button class="btn orange" type="submit"><x-icon name="search" size="16" /> Track order</button>
        @if ($error)<p class="alert bad" role="alert">{{ $error }}</p>@endif
    </form>

    @if ($order)
        <section class="tresult" aria-labelledby="t-st">
            <h2 id="t-st">Order {{ $order->number }} <span @class(['stag', 'st-'.$order->status])>{{ $order->statusLabel() }}</span></h2>
            @if (in_array($order->status, ['cancelled', 'returned'], true))
                <p class="alert bad">This order was {{ strtolower($order->statusLabel()) }}.</p>
            @else
                @php $at = array_search($order->status, $flow, true); @endphp
                <ol class="tline">
                    @foreach ($flow as $i => $st)
                        <li @class(['done' => $i <= $at, 'now' => $i === $at])>
                            <span class="tdot2">@if ($i <= $at)<x-icon name="check" size="14" stroke="2.6" />@endif</span>
                            {{ Order::STATUSES[$st] }}
                        </li>
                    @endforeach
                </ol>
            @endif
            @if ($order->tracking_code)<p>Courier tracking code: <b>{{ $order->tracking_code }}</b></p>@endif
            <ul class="hist">
                @foreach ($order->histories as $h)
                    <li><b>{{ Order::STATUSES[$h->status] ?? $h->status }}</b> <span>{{ $h->created_at->format('j M Y, g:i a') }}</span>@if ($h->note)<br><small>{{ $h->note }}</small>@endif</li>
                @endforeach
            </ul>
            @include('orders._items')
        </section>
    @endif
</div>
@endsection
