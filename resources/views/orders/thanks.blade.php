@extends('layouts.app')

@section('title', 'Thank you - Order '.$order->number.' - SafeBuy')

@section('content')
@php use App\Models\Product; @endphp
<div class="wrap sec-in thanks">
    <div class="tk-hero">
        <span class="tk-check" aria-hidden="true"><x-icon name="check" size="40" stroke="2.6" /></span>
        <h1>Thank you, {{ $order->name }}!</h1>
        <p>Your order has been received. We will call <b>{{ $order->phone }}</b> to confirm it.</p>
        @if ($order->payment_method === 'bkash')
            <p class="alert">We are checking your bKash payment (Transaction ID <b>{{ $order->bkash_trx_id }}</b>). Your order is sent once it is confirmed.</p>
        @endif
    </div>

    <ul class="tk-facts">
        <li><span>Order number</span><b>{{ $order->number }}</b></li>
        <li><span>Date</span><b>{{ $order->created_at->format('j M Y') }}</b></li>
        <li><span>Total</span><b>{{ Product::taka($order->total) }}</b></li>
        <li><span>Payment</span><b>{{ $order->paymentLabel() }}</b></li>
    </ul>

    <div class="tk-grid">
        <section aria-labelledby="t-od">
            <h2 id="t-od">Order details</h2>
            @include('orders._items')
        </section>
        <section aria-labelledby="t-addr" class="tk-addr">
            <h2 id="t-addr">Delivery address</h2>
            @if ($order->ship_name)
                <p><b>{{ $order->ship_name }}</b><br>{{ $order->ship_phone }}<br>{{ $order->ship_address }}<br>{{ $order->ship_area ? $order->ship_area.', ' : '' }}{{ $order->ship_district }}</p>
            @else
                <p><b>{{ $order->name }}</b><br>{{ $order->phone }}<br>{{ $order->address }}<br>{{ $order->area ? $order->area.', ' : '' }}{{ $order->district }}</p>
            @endif
            @if ($order->note)<p class="small">Note: {{ $order->note }}</p>@endif
            <div class="tk-btns">
                <a class="btn blue" href="{{ route('order.track', ['number' => $order->number, 'phone' => $order->phone]) }}">Track this order</a>
                <a class="btn ghost" href="{{ route('shop') }}">Continue shopping</a>
            </div>
        </section>
    </div>
</div>
@endsection
