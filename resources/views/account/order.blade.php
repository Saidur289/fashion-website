@extends('layouts.app')

@section('title', 'Order '.$order->number.' - SafeBuy')

@section('content')
@php use App\Models\Order; $flow = Order::FLOW; @endphp
@include('partials.page-head', ['title' => 'Order '.$order->number, 'crumbs' => [['My account', route('account')], ['Orders', route('account.orders')], [$order->number, null]]])
<div class="wrap sec-in acc">
    @include('account._shell', ['active' => 'orders'])
    <div class="acc-main tresult">
        <p>Placed on <b>{{ $order->created_at->format('j M Y, g:i a') }}</b> · <span @class(['stag', 'st-'.$order->status])>{{ $order->statusLabel() }}</span></p>
        @unless (in_array($order->status, ['cancelled', 'returned'], true))
            @php $at = array_search($order->status, $flow, true); @endphp
            <ol class="tline">
                @foreach ($flow as $i => $st)
                    <li @class(['done' => $i <= $at, 'now' => $i === $at])><span class="tdot2">@if ($i <= $at)<x-icon name="check" size="14" stroke="2.6" />@endif</span>{{ Order::STATUSES[$st] }}</li>
                @endforeach
            </ol>
        @endunless
        @if ($order->tracking_code)<p>Courier tracking code: <b>{{ $order->tracking_code }}</b></p>@endif
        @include('orders._items')
        <div class="tk-grid" style="margin-top: 24px">
            <section class="tk-addr">
                <h2 class="acc-h">Delivery address</h2>
                <p><b>{{ $order->ship_name ?: $order->name }}</b><br>{{ $order->ship_phone ?: $order->phone }}<br>{{ $order->ship_address ?: $order->address }}<br>{{ ($order->ship_area ?: $order->area) ? ($order->ship_area ?: $order->area).', ' : '' }}{{ $order->ship_district ?: $order->district }}</p>
            </section>
            <section>
                <h2 class="acc-h">Need help?</h2>
                <p class="small">Contact us at <a href="mailto:{{ config('safebuy.email') }}">{{ config('safebuy.email') }}</a>@if (config('safebuy.phone')) or call {{ config('safebuy.phone') }}@endif and mention order <b>{{ $order->number }}</b>.</p>
                @if ($order->status === 'delivered')
                    <p class="small">Happy with your products? You can now review them on each product page.</p>
                    <ul class="rev-links">
                        @foreach ($order->items as $item)
                            @if ($item->product)<li><a class="tlink" href="{{ route('product.show', $item->product) }}#reviews">Review {{ \Illuminate\Support\Str::limit($item->name, 40) }}</a></li>@endif
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
